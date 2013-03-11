<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\CmsManager;

use Sonata\BlockBundle\Model\BlockInterface;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\BlockInteractorInterface;

/**
 * The CmsPageManager class is in charge of retrieving the correct page (cms page or action page)
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CmsPageManager extends BaseCmsPageManager
{
    protected $blockInteractor;

    protected $pageManager;

    protected $pageReferences = array();

    protected $pages = array();

    /**
     * @param \Sonata\PageBundle\Model\PageManagerInterface     $pageManager
     * @param \Sonata\PageBundle\Model\BlockInteractorInterface $blockInteractor
     */
    public function __construct(PageManagerInterface $pageManager, BlockInteractorInterface $blockInteractor)
    {
        $this->pageManager     = $pageManager;
        $this->blockInteractor = $blockInteractor;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage(SiteInterface $site, $page)
    {
        if (is_string($page) && substr($page, 0, 1) == '/') {
            $page = $this->getPageByUrl($site, $page);
        } elseif (is_string($page)) { // page is a slug, load the related page
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

    /**
     * {@inheritdoc}
     */
    public function getInternalRoute(SiteInterface $site, $pageName)
    {
        if (substr($pageName, 0, 5) == 'error') {
            throw new \RuntimeException(sprintf('Illegal internal route name : %s, an internal page cannot start with `error`', $pageName));
        }

        $routeName = sprintf('_page_internal_%s', $pageName);

        try {
            $page = $this->getPageByRouteName($site, $routeName);
        } catch (PageNotFoundException $e) {
            $page = $this->pageManager->create(array(
                'url'       => null,
                'routeName' => $routeName,
                'name'      => sprintf(sprintf('Internal Page : %s', $pageName)),
                'decorate'  => false,
            ));

            $page->setSite($site);

            $this->pageManager->save($page);
        }

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function findContainer($code, PageInterface $page, BlockInterface $parentContainer = null)
    {
        $container = null;

        if ($parentContainer) {
            // parent container is set, nothing to find, don't need to loop across the
            // name to find the correct container (main template level)
            $container = $parentContainer;
        }

        // first level blocks are containers
        if (!$container && $page->getBlocks()) {
            foreach ($page->getBlocks() as $block) {
                if ($block->getSetting('code') == $code) {

                    $container = $block;
                    break;
                }
            }
        }

        if (!$container) {
            $container = $this->blockInteractor->createNewContainer(array(
                'enabled'  => true,
                'page'     => $page,
                'code'     => $code,
                'position' => 1,
                'parent'   => $parentContainer
            ));
        }

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPageBy(SiteInterface $site = null, $fieldName, $value)
    {
        if ('id' == $fieldName) {
            $id = $value;
        } elseif (isset($this->pageReferences[$fieldName][$value])) {
            $id = $this->pageReferences[$fieldName][$value];
        } else {
            $id = null;
        }

        if (null === $id || !isset($this->pages[$id])) {
            $this->pages[$id] = false;

            $parameters = array(
                $fieldName => $value,
            );

            if ($site) {
                $parameters['site'] = $site->getId();
            }

            $page = $this->pageManager->findOneBy($parameters);

            if (!$page) {
                throw new PageNotFoundException(sprintf('Unable to find the page : %s = %s', $fieldName, $value));
            }

            $this->loadBlocks($page);
            $id = $page->getId();

            if ($fieldName != 'id') {
                $this->pageReferences[$fieldName][$value] = $id;
            }

            $this->pages[$id] = $page;
        }

        return $this->pages[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getBlock($id)
    {
        if (!isset($this->blocks[$id])) {
            $this->blocks[$id] = $this->blockInteractor->getBlock($id);
        }

        return $this->blocks[$id];
    }

    /**
     * load all the related nested blocks linked to one page.
     *
     * @param PageInterface $page
     */
    private function loadBlocks(PageInterface $page)
    {
        $blocks = $this->blockInteractor->loadPageBlocks($page);

        // save a local cache
        foreach ($blocks as $block) {
            $this->blocks[$block->getId()] = $block;
        }
    }
}
