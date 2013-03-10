<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Entity;

use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;

/**
 * This class transform a SnapshotInterface into PageInterface
 *
 * @package Sonata\PageBundle\Entity
 */
class Transformer implements TransformerInterface
{
    protected $snapshotManager;

    protected $pageManager;

    /**
     * @param SnapshotManagerInterface $snapshotManager
     * @param PageManagerInterface     $pageManager
     */
    public function __construct(SnapshotManagerInterface $snapshotManager, PageManagerInterface $pageManager)
    {
        $this->snapshotManager = $snapshotManager;
        $this->pageManager = $pageManager;
    }

    /**
     * {@inheritdoc}
     */
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

        if ($page->getTarget()) {
            $snapshot->setTargetId($page->getTarget()->getId());
        }

        $content                     = array();
        $content['id']               = $page->getId();
        $content['name']             = $page->getName();
        $content['javascript']       = $page->getJavascript();
        $content['stylesheet']       = $page->getStylesheet();
        $content['raw_headers']      = $page->getRawHeaders();
        $content['title']            = $page->getTitle();
        $content['meta_description'] = $page->getMetaDescription();
        $content['meta_keyword']     = $page->getMetaKeyword();
        $content['template_code']    = $page->getTemplateCode();
        $content['request_method']   = $page->getRequestMethod();
        $content['created_at']       = $page->getCreatedAt()->format('U');
        $content['updated_at']       = $page->getUpdatedAt()->format('U');
        $content['slug']             = $page->getSlug();
        $content['parent_id']        = $page->getParent() ? $page->getParent()->getId() : false;
        $content['target_id']        = $page->getTarget() ? $page->getTarget()->getId() : false;

        $content['blocks'] = array();
        foreach ($page->getBlocks() as $block) {
            $content['blocks'][] = $this->createBlocks($block);
        }

        $snapshot->setContent($content);

        return $snapshot;
    }


    /**
     * {@inheritdoc}
     */
    public function load(SnapshotInterface $snapshot)
    {
        $page = new $this->pageClass;

        $page->setRouteName($snapshot->getRouteName());
        $page->setPageAlias($snapshot->getPageAlias());
        $page->setType($snapshot->getType());
        $page->setCustomUrl($snapshot->getUrl());
        $page->setUrl($snapshot->getUrl());
        $page->setPosition($snapshot->getPosition());
        $page->setDecorate($snapshot->getDecorate());
        $page->setSite($snapshot->getSite());
        $page->setEnabled($snapshot->getEnabled());

        $content = $this->fixPageContent($snapshot->getContent());

        $page->setId($content['id']);
        $page->setJavascript($content['javascript']);
        $page->setStylesheet($content['stylesheet']);
        $page->setRawHeaders($content['raw_headers']);
        $page->setTitle($content['title']);
        $page->setMetaDescription($content['meta_description']);
        $page->setMetaKeyword($content['meta_keyword']);
        $page->setName($content['name']);
        $page->setSlug($content['slug']);
        $page->setTemplateCode($content['template_code']);
        $page->setRequestMethod($content['request_method']);

        $createdAt = new \DateTime;
        $createdAt->setTimestamp($content['created_at']);
        $page->setCreatedAt($createdAt);

        $updatedAt = new \DateTime;
        $updatedAt->setTimestamp($content['updated_at']);
        $page->setUpdatedAt($updatedAt);

        return $page;
    }

    /**
     * @param array $content
     *
     * @return array
     */
    protected function fixPageContent(array $content)
    {
        if (!array_key_exists('title', $content)) {
            $content['title'] = null;
        }

        return $content;
    }

    /**
     * @param array $content
     *
     * @return array
     */
    protected function fixBlockContent(array $content)
    {
        if (!array_key_exists('name', $content)) {
            $content['name'] = null;
        }

        return $content;
    }
    /**
     * @param array         $content
     * @param PageInterface $page
     *
     * @return BlockInterface
     */
    protected function loadBlock(array $content, PageInterface $page)
    {
        $block = new $this->blockClass;

        $content = $this->fixBlockContent($content);

        $block->setPage($page);
        $block->setId($content['id']);
        $block->setName($content['name']);
        $block->setEnabled($content['enabled']);
        $block->setPosition($content['position']);
        $block->setSettings($content['settings']);
        $block->setType($content['type']);

        $createdAt = new \DateTime;
        $createdAt->setTimestamp($content['created_at']);
        $block->setCreatedAt($createdAt);

        $updatedAt = new \DateTime;
        $updatedAt->setTimestamp($content['updated_at']);
        $block->setUpdatedAt($updatedAt);

        foreach ($content['blocks'] as $child) {
            $block->addChildren($this->loadBlock($child, $page));
        }

        return $block;
    }

    /**
     * @param BlockInterface $block
     *
     * @return array
     */
    protected function createBlocks(BlockInterface $block)
    {
        $content = array();
        $content['id']       = $block->getId();
        $content['name']     = $block->getName();
        $content['enabled']  = $block->getEnabled();
        $content['position'] = $block->getPosition();
        $content['settings'] = $block->getSettings();
        $content['type']     = $block->getType();
        $content['created_at'] = $block->getCreatedAt()->format('U');
        $content['updated_at'] = $block->getUpdatedAt()->format('U');
        $content['blocks']   = array();

        foreach ($block->getChildren() as $child) {
            $content['blocks'][] = $this->createBlocks($child);
        }

        return $content;
    }
}