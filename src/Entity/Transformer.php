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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * This class transform a SnapshotInterface into PageInterface.
 *
 * @phpstan-import-type PageContent from TransformerInterface
 * @phpstan-import-type BlockContent from TransformerInterface
 */
final class Transformer implements TransformerInterface
{
    private SnapshotManagerInterface $snapshotManager;

    private PageManagerInterface $pageManager;

    /**
     * @var ManagerInterface<PageBlockInterface>
     */
    private ManagerInterface $blockManager;

    /**
     * @var array<Collection<array-key, PageInterface>>
     */
    private array $children = [];

    private ManagerRegistry $registry;

    /**
     * @param ManagerInterface<PageBlockInterface> $blockManager
     */
    public function __construct(
        SnapshotManagerInterface $snapshotManager,
        PageManagerInterface $pageManager,
        ManagerInterface $blockManager,
        ManagerRegistry $registry
    ) {
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

        $blocks = [];
        foreach ($page->getBlocks() as $block) {
            if ($block->getParent()) { // ignore block with a parent => must be a child of a main
                continue;
            }

            $blocks[] = $this->createBlock($block);
        }

        $snapshot->setContent([
            'id' => $page->getId(),
            'name' => $page->getName(),
            'javascript' => $page->getJavascript(),
            'stylesheet' => $page->getStylesheet(),
            'raw_headers' => $page->getRawHeaders(),
            'title' => $page->getTitle(),
            'meta_description' => $page->getMetaDescription(),
            'meta_keyword' => $page->getMetaKeyword(),
            'template_code' => $page->getTemplateCode(),
            'request_method' => $page->getRequestMethod(),
            'created_at' => null !== $page->getCreatedAt() ? (int) $page->getCreatedAt()->format('U') : null,
            'updated_at' => null !== $page->getUpdatedAt() ? (int) $page->getUpdatedAt()->format('U') : null,
            'slug' => $page->getSlug(),
            'parent_id' => $page->getParent() ? $page->getParent()->getId() : null,
            'blocks' => $blocks,
        ]);

        return $snapshot;
    }

    public function load(SnapshotInterface $snapshot)
    {
        $page = $this->pageManager->createWithDefaults();

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

        if (null !== $content) {
            $page->setId($content['id']);
            $page->setJavascript($content['javascript']);
            $page->setStylesheet($content['stylesheet']);
            $page->setRawHeaders($content['raw_headers']);
            $page->setTitle($content['title'] ?? null);
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
        }

        return $page;
    }

    public function loadBlock(array $content, PageInterface $page)
    {
        $block = $this->blockManager->create();

        $block->setPage($page);

        if (isset($content['id'])) {
            $block->setId($content['id']);
        }

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
            $block->addChild($this->loadBlock($child, $page));
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

            /**
             * @var Collection<array-key, PageInterface>
             */
            $collection = new ArrayCollection();

            foreach ($snapshots as $snapshot) {
                $collection->add($this->snapshotManager->createSnapshotPageProxy($this, $snapshot));
            }

            $this->children[$page->getId()] = $collection;
        }

        return $this->children[$page->getId()];
    }

    /**
     * @return array<string, mixed>
     *
     * @phpstan-return BlockContent
     */
    private function createBlock(BlockInterface $block)
    {
        $childBlocks = [];

        foreach ($block->getChildren() as $child) {
            $childBlocks[] = $this->createBlock($child);
        }

        return [
            'id' => $block->getId(),
            'name' => $block->getName(),
            'enabled' => $block->getEnabled(),
            'position' => $block->getPosition(),
            'settings' => $block->getSettings(),
            'type' => $block->getType(),
            'created_at' => null !== $block->getCreatedAt() ? (int) $block->getCreatedAt()->format('U') : null,
            'updated_at' => null !== $block->getUpdatedAt() ? (int) $block->getUpdatedAt()->format('U') : null,
            'blocks' => $childBlocks,
        ];
    }
}
