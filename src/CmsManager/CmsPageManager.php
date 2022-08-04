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

use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class CmsPageManager extends BaseCmsPageManager
{
    private BlockInteractorInterface $blockInteractor;

    private PageManagerInterface $pageManager;

    /**
     * @var array{
     *   url: array<int|string|null>,
     *   routeName: array<int|string|null>,
     *   pageAlias: array<int|string|null>,
     *   name: array<int|string|null>,
     * }
     */
    private array $pageReferences = [
        'url' => [],
        'routeName' => [],
        'pageAlias' => [],
        'name' => [],
    ];

    /**
     * @var PageInterface[]
     */
    private array $pages = [];

    public function __construct(PageManagerInterface $pageManager, BlockInteractorInterface $blockInteractor)
    {
        $this->pageManager = $pageManager;
        $this->blockInteractor = $blockInteractor;
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
            throw new PageNotFoundException('Unable to retrieve the page');
        }

        return $page;
    }

    public function getInternalRoute(SiteInterface $site, $routeName)
    {
        if ('error' === substr($routeName, 0, 5)) {
            throw new \RuntimeException(sprintf('Illegal internal route name : %s, an internal page cannot start with `error`', $routeName));
        }

        $routeName = sprintf('_page_internal_%s', $routeName);

        try {
            $page = $this->getPageByRouteName($site, $routeName);
        } catch (PageNotFoundException $e) {
            $page = $this->pageManager->createWithDefaults([
                'url' => null,
                'routeName' => $routeName,
                'name' => sprintf('Internal Page : %s', $routeName),
                'decorate' => false,
            ]);

            $page->setSite($site);

            $this->pageManager->save($page);
        }

        return $page;
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

        if (!$container) {
            $container = $this->blockInteractor->createNewContainer([
                'enabled' => true,
                'page' => $page,
                'code' => $name,
                'position' => 1,
                'parent' => $parentContainer,
            ]);
        }

        return $container;
    }

    public function getBlock($id)
    {
        if (!\array_key_exists($id, $this->blocks)) {
            $this->blocks[$id] = $this->blockInteractor->getBlock($id);
        }

        return $this->blocks[$id];
    }

    protected function getPageBy(?SiteInterface $site, $fieldName, $value)
    {
        if ('id' === $fieldName) {
            $id = $value;
        } elseif (isset($this->pageReferences[$fieldName][$value])) {
            $id = $this->pageReferences[$fieldName][$value];
        } else {
            $id = null;
        }

        if (null === $id || !isset($this->pages[$id])) {
            $parameters = [
                $fieldName => $value,
            ];

            if ($site) {
                $parameters['site'] = $site->getId();
            }

            $page = $this->pageManager->findOneBy($parameters);

            if (!$page) {
                throw new PageNotFoundException(sprintf('Unable to find the page : %s = %s', $fieldName, $value));
            }

            $this->loadBlocks($page);

            $id = $page->getId();

            if ('id' !== $fieldName) {
                $this->pageReferences[$fieldName][$value] = $id;
            }

            $this->pages[$id] = $page;
        }

        return $this->pages[$id];
    }

    private function loadBlocks(PageInterface $page): void
    {
        $blocks = $this->blockInteractor->loadPageBlocks($page);

        // save a local cache
        foreach ($blocks as $block) {
            $this->blocks[$block->getId()] = $block;
        }
    }
}
