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

namespace Sonata\PageBundle\CmsManager;

use Sonata\BlockBundle\Util\RecursiveBlockIteratorIterator;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * The CmsSnapshotManager class is in charge of retrieving the correct page (cms page or action page).
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class CmsSnapshotManager extends BaseCmsPageManager
{
    private SnapshotManagerInterface $snapshotManager;

    private TransformerInterface $transformer;

    private array $pageReferences = [];

    /**
     * @var PageInterface[]
     */
    private array $pages = [];

    public function __construct(SnapshotManagerInterface $snapshotManager, TransformerInterface $transformer)
    {
        $this->snapshotManager = $snapshotManager;
        $this->transformer = $transformer;
    }

    public function getPage(SiteInterface $site, $page)
    {
        if (\is_string($page) && '/' === substr($page, 0, 1)) {
            $page = $this->getPageByUrl($site, $page);
        } elseif (\is_string($page)) { // page is a slug, load the related page
            $page = $this->getPageByRouteName($site, $page);
        } elseif (is_numeric($page)) {
            $page = $this->getPageById($page);
        } elseif (!$page) { // get the current page
            $page = $this->getCurrentPage();
        }

        if (!$page instanceof PageInterface) {
            throw new PageNotFoundException('Unable to retrieve the snapshot');
        }

        return $page;
    }

    public function getInternalRoute(SiteInterface $site, $routeName)
    {
        return $this->getPageByRouteName($site, sprintf('_page_internal_%s', $routeName));
    }

    public function findContainer($name, PageInterface $page, ?PageBlockInterface $parentContainer = null)
    {
        $container = null;

        if ($parentContainer) {
            // parent container is set, nothing to find, don't need to loop across the
            // name to find the correct container (main template level)
            $container = $parentContainer;
        }

        // first level blocks are containers
        if (!$container) {
            foreach ($page->getBlocks() as $block) {
                if ($block->getSetting('code') === $name) {
                    $container = $block;

                    break;
                }
            }
        }

        return $container;
    }

    public function getBlock($id)
    {
        if (isset($this->blocks[$id])) {
            return $this->blocks[$id];
        }

        return null;
    }

    protected function getPageBy(?SiteInterface $site, $fieldName, $value)
    {
        if ('id' === $fieldName) {
            $fieldName = 'pageId';
            $id = $value;
        } elseif (isset($this->pageReferences[$fieldName][$value])) {
            $id = $this->pageReferences[$fieldName][$value];
        } else {
            $id = null;
        }

        if (null === $id || !isset($this->pages[$id])) {
            $parameters = [$fieldName => $value];

            if ($site) {
                $parameters['site'] = $site->getId();
            }

            $snapshot = $this->snapshotManager->findEnableSnapshot($parameters);

            if (!$snapshot) {
                throw new PageNotFoundException();
            }

            $page = $this->snapshotManager->createSnapshotPageProxy($this->transformer, $snapshot);

            $this->pages[$id] = false;

            $this->loadBlocks($page);

            $id = $page->getId();

            if ('id' !== $fieldName) {
                $this->pageReferences[$fieldName][$value] = $id;
            }

            $this->pages[$id] = $page;
        }

        return $this->pages[$id];
    }

    /**
     * load the blocks of the $page.
     */
    private function loadBlocks(PageInterface $page): void
    {
        $iterator = new RecursiveBlockIteratorIterator($page->getBlocks());

        foreach ($iterator as $block) {
            $this->blocks[$block->getId()] = $block;
        }
    }
}
