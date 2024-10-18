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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class CmsSnapshotManager extends BaseCmsPageManager
{
    /**
     * @var array{
     *   url: array<int|string|null>,
     *   routeName: array<int|string|null>,
     *   pageAlias: array<int|string|null>,
     *   name: array<int|string|null>,
     *   pageId: array<int|string|null>,
     * }
     */
    private array $pageReferences = [
        'url' => [],
        'routeName' => [],
        'pageAlias' => [],
        'name' => [],
        'pageId' => [],
    ];

    /**
     * @var array<PageInterface>
     */
    private array $pages = [];

    public function __construct(
        private SnapshotManagerInterface $snapshotManager,
        private TransformerInterface $transformer,
    ) {
    }

    public function getPage(SiteInterface $site, $page): PageInterface
    {
        if (\is_string($page) && str_starts_with($page, '/')) {
            $page = $this->getPageByUrl($site, $page);
        } elseif (\is_string($page)) { // page is a slug, load the related page
            $page = $this->getPageByRouteName($site, $page);
        } elseif (is_numeric($page)) {
            $page = $this->getPageById($page);
        } elseif (null === $page) { // get the current page
            $page = $this->getCurrentPage();
        }

        if (!$page instanceof PageInterface) {
            throw new PageNotFoundException('Unable to retrieve the snapshot');
        }

        return $page;
    }

    public function getInternalRoute(SiteInterface $site, string $routeName): PageInterface
    {
        return $this->getPageByRouteName($site, \sprintf('_page_internal_%s', $routeName));
    }

    public function findContainer(string $name, PageInterface $page, ?PageBlockInterface $parentContainer = null): ?PageBlockInterface
    {
        $container = null;

        if (null !== $parentContainer) {
            // parent container is set, nothing to find, don't need to loop across the
            // name to find the correct container (main template level)
            $container = $parentContainer;
        }

        // first level blocks are containers
        if (null === $container) {
            foreach ($page->getBlocks() as $block) {
                if ($block->getSetting('code') === $name) {
                    $container = $block;

                    break;
                }
            }
        }

        return $container;
    }

    public function getBlock($id): ?PageBlockInterface
    {
        return $this->blocks[$id] ?? null;
    }

    protected function getPageBy(?SiteInterface $site, string $fieldName, $value): PageInterface
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

            if (null !== $site) {
                $parameters['site'] = $site->getId();
            }

            $snapshot = $this->snapshotManager->findEnableSnapshot($parameters);

            if (null === $snapshot) {
                throw new PageNotFoundException();
            }

            $page = $this->snapshotManager->createSnapshotPageProxy($this->transformer, $snapshot);

            $this->loadBlocks($page);

            $id = $page->getId();
            \assert(null !== $id);

            $this->pageReferences[$fieldName][$value] = $id;
            $this->pages[$id] = $page;
        }

        return $this->pages[$id];
    }

    private function loadBlocks(PageInterface $page): void
    {
        $iterator = new RecursiveBlockIteratorIterator($page->getBlocks());

        foreach ($iterator as $block) {
            $this->blocks[$block->getId()] = $block;
        }
    }
}
