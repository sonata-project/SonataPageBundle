<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * This class transform a SnapshotInterface into PageInterface.
 *
 * @final since sonata-project/page-bundle 3.26
 */
class Transformer implements TransformerInterface
{
    /**
     * @var SnapshotManagerInterface
     */
    protected $snapshotManager;

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var BlockManagerInterface
     */
    protected $blockManager;

    /**
     * @var BlockInterface[]
     */
    protected $children = [];

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    public function __construct(SnapshotManagerInterface $snapshotManager, PageManagerInterface $pageManager, BlockManagerInterface $blockManager, ManagerRegistry $registry)
    {
        $this->snapshotManager = $snapshotManager;
        $this->pageManager = $pageManager;
        $this->blockManager = $blockManager;
        $this->registry = $registry;
    }

    public function create(PageInterface $page)
    {
        $snapshot = $this->snapshotManager->create();

        $snapshot->setPage($page);
        $snapshot->setUrl($page->getUrl());
        $snapshot->setEnabled($page->getEnabled());
        $snapshot->setRouteName($page->getRouteName());
        $snapshot->setPageAlias($page->getPageAlias());
        $snapshot->setType($page->getType());
        $snapshot->setName($page->getName());
        $snapshot->setPosition($page->getPosition());
        $snapshot->setDecorate($page->getDecorate());

        if (!$page->getSite()) {
            throw new \RuntimeException(sprintf('No site linked to the page.id=%s', $page->getId()));
        }

        $snapshot->setSite($page->getSite());

        if ($page->getParent()) {
            $snapshot->setParentId($page->getParent()->getId());
        }

        //NEXT_MAJOR: Remove this "if" condition block.
        if ($page->getTarget()) {
            @trigger_error(
                'target page is deprecate since sonata-project/page-bundle 3.27.0'.
                ', and it will be removed in 4.0',
                \E_USER_DEPRECATED
            );
            $snapshot->setTargetId($page->getTarget()->getId());
        }

        $content = [];
        $content['id'] = $page->getId();
        $content['name'] = $page->getName();
        $content['javascript'] = $page->getJavascript();
        $content['stylesheet'] = $page->getStylesheet();
        $content['raw_headers'] = $page->getRawHeaders();
        $content['title'] = $page->getTitle();
        $content['meta_description'] = $page->getMetaDescription();
        $content['meta_keyword'] = $page->getMetaKeyword();
        $content['template_code'] = $page->getTemplateCode();
        $content['request_method'] = $page->getRequestMethod();
        $content['created_at'] = $page->getCreatedAt()->format('U');
        $content['updated_at'] = $page->getUpdatedAt()->format('U');
        $content['slug'] = $page->getSlug();
        $content['parent_id'] = $page->getParent() ? $page->getParent()->getId() : null;
        $content['target_id'] = $page->getTarget() ? $page->getTarget()->getId() : null; //NEXT_MAJOR: Remove this line.

        $content['blocks'] = [];
        foreach ($page->getBlocks() as $block) {
            if ($block->getParent()) { // ignore block with a parent => must be a child of a main
                continue;
            }

            $content['blocks'][] = $this->createBlocks($block);
        }

        $snapshot->setContent($content);

        return $snapshot;
    }

    public function load(SnapshotInterface $snapshot)
    {
        $page = $this->pageManager->create();

        $page->setRouteName($snapshot->getRouteName());
        $page->setPageAlias($snapshot->getPageAlias());
        $page->setType($snapshot->getType());
        $page->setCustomUrl($snapshot->getUrl());
        $page->setUrl($snapshot->getUrl());
        $page->setPosition($snapshot->getPosition());
        $page->setDecorate($snapshot->getDecorate());
        $page->setSite($snapshot->getSite());
        $page->setEnabled($snapshot->getEnabled());

        $content = $snapshot->getContent();

        $page->setId($content['id']);
        $page->setJavascript($content['javascript']);
        $page->setStylesheet($content['stylesheet']);
        $page->setRawHeaders($content['raw_headers']);
        if (isset($content['title'])) {
            $page->setTitle($content['title']);
        }
        $page->setMetaDescription($content['meta_description']);
        $page->setMetaKeyword($content['meta_keyword']);
        $page->setName($content['name']);
        $page->setSlug($content['slug']);
        $page->setTemplateCode($content['template_code']);
        $page->setRequestMethod($content['request_method']);

        $createdAt = new \DateTime();
        $createdAt->setTimestamp((int) $content['created_at']);
        $page->setCreatedAt($createdAt);

        $updatedAt = new \DateTime();
        $updatedAt->setTimestamp((int) $content['updated_at']);
        $page->setUpdatedAt($updatedAt);

        return $page;
    }

    public function loadBlock(array $content, PageInterface $page)
    {
        $block = $this->blockManager->create();

        $block->setPage($page);
        $block->setId($content['id']);
        if (isset($content['name'])) {
            $block->setName($content['name']);
        }
        $block->setEnabled($content['enabled']);
        if (isset($content['position'])) {
            $block->setPosition($content['position']);
        }
        $block->setSettings($content['settings']);
        if (isset($content['type'])) {
            $block->setType($content['type']);
        }

        $createdAt = new \DateTime();
        $createdAt->setTimestamp((int) $content['created_at']);
        $block->setCreatedAt($createdAt);

        $updatedAt = new \DateTime();
        $updatedAt->setTimestamp((int) $content['updated_at']);
        $block->setUpdatedAt($updatedAt);

        foreach ($content['blocks'] as $child) {
            $block->addChildren($this->loadBlock($child, $page));
        }

        return $block;
    }

    public function getChildren(PageInterface $page)
    {
        if (!isset($this->children[$page->getId()])) {
            $date = new \DateTime();
            $parameters = [
                'publicationDateStart' => $date,
                'publicationDateEnd' => $date,
                'parentId' => $page->getId(),
            ];

            $manager = $this->registry->getManagerForClass($this->snapshotManager->getClass());

            if (!$manager instanceof EntityManagerInterface) {
                throw new \RuntimeException('Invalid entity manager type');
            }

            $snapshots = $manager->createQueryBuilder()
                ->select('s')
                ->from($this->snapshotManager->getClass(), 's')
                ->where('s.parentId = :parentId and s.enabled = 1')
                ->andWhere('s.publicationDateStart <= :publicationDateStart AND ( s.publicationDateEnd IS NULL OR s.publicationDateEnd >= :publicationDateEnd )')
                ->orderBy('s.position')
                ->setParameters($parameters)
                ->getQuery()
                ->execute();

            $pages = [];

            foreach ($snapshots as $snapshot) {
                if (method_exists($this->snapshotManager, 'createSnapshotPageProxy')) {
                    $page = $this->snapshotManager->createSnapshotPageProxy($this, $snapshot);
                } else {
                    $page = new SnapshotPageProxy($this->snapshotManager, $this, $snapshot);
                }
                $pages[$page->getId()] = $page;
            }

            $this->children[$page->getId()] = new ArrayCollection($pages);
        }

        return $this->children[$page->getId()];
    }

    /**
     * @return array<string, mixed>
     *
     * @phpstan-return BlockContent
     */
    protected function createBlocks(BlockInterface $block)
    {
        $content = [];
        $content['id'] = $block->getId();
        $content['name'] = $block->getName();
        $content['enabled'] = $block->getEnabled();
        $content['position'] = $block->getPosition();
        $content['settings'] = $block->getSettings();
        $content['type'] = $block->getType();
        $content['created_at'] = $block->getCreatedAt()->format('U');
        $content['updated_at'] = $block->getUpdatedAt()->format('U');
        $content['blocks'] = [];

        foreach ($block->getChildren() as $child) {
            $content['blocks'][] = $this->createBlocks($child);
        }

        return $content;
    }
}
