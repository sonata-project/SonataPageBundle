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

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Block\BlockServiceInterface;
use Sonata\PageBundle\Cache\CacheInterface;
use Sonata\PageBundle\Util\RecursiveBlockIterator;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\PageBundle\Cache\Invalidation\InvalidationInterface;

use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CmsSnapshotManager extends BaseCmsPageManager
{
    /**
     * @param \Sonata\PageBundle\Model\SnapshotManagerInterface $pageManager
     * @param \Symfony\Component\Templating\EngineInterface $templating
     * @param \Sonata\PageBundle\Cache\Invalidation\InvalidationInterface $cacheInvalidation
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(
        SnapshotManagerInterface $pageManager,
        EngineInterface $templating,
        InvalidationInterface $cacheInvalidation,
        RouterInterface $router
    )
    {
        $this->pageManager        = $pageManager;
        $this->templating         = $templating;
        $this->cacheInvalidation  = $cacheInvalidation;
        $this->router             = $router;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'snapshot';
    }

    /**
     * Return a PageInterface instance depends on the $page argument
     *
     * @param mixed $page
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPage($page)
    {
        if (is_string($page) && substr($page, 0, 1) == '/') {
            $page = $this->getPageByUrl($page);
        } else if (is_string($page)) { // page is a slug, load the related page
            $page = $this->getPageByRouteName($page);
        } else if ( is_numeric($page)) {
            $page = $this->getPageById($page);
        } else if (!$page) { // get the current page
            $page = $this->getCurrentPage();
        }

        if (!$page instanceof PageInterface) {
            throw new \RunTimeException('Unable to retrieve the snapshot');
        }

        return $page;
    }

    /**
     * @param string $name
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param null|\Sonata\PageBundle\Model\BlockInterface $parentContainer
     * @return bool|null|\Sonata\PageBundle\Model\BlockInterface
     */
    public function findContainer($name, PageInterface $page, BlockInterface $parentContainer = null)
    {
        $container = false;

        if ($parentContainer) {
            // parent container is set, nothing to find, don't need to loop across the
            // name to find the correct container (main template level)
            $container = $parentContainer;
        }

        // first level blocks are containers
        if (!$container && $page->getBlocks()) {
            foreach ($page->getBlocks() as $block) {
                if ($block->getSetting('name') == $name) {
                    $container = $block;
                    break;
                }
            }
        }

        return $container;
    }

    /**
     * return a fully loaded page ( + blocks ) from a url
     *
     * @param string $url
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageByUrl($url)
    {
        return $this->getPageBy('url', $url);
    }


    /**
     * return a fully loaded page ( + blocks ) from a id
     *
     * @param integer $id
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageById($id)
    {
        return $this->getPageBy('pageId', $id);
    }

    /**
     * return a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param string $routeName
     * @param boolean $create default true
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageByRouteName($routeName, $create = true)
    {
        if (!isset($this->routePages[$routeName])) {
            $this->routePages[$routeName] = $this->getPageBy('routeName', $routeName);
        }

        return $this->routePages[$routeName];
    }

    public function renderContainer($name, $page = null, BlockInterface $parentContainer = null)
    {
        try {
            $response = parent::renderContainer($name, $page, $parentContainer);

            return $response;
        } catch(\RunTimeException $e) {

            // silently fail error message
            return '';
        }

    }

    /**
     * return a fully loaded page ( + blocks ) whose match with the $value of the $fieldName
     *
     * @param string $fieldName
     * @param string $value
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageBy($fieldName, $value)
    {
        $snapshot = $this->getPageManager()->findEnableSnapshot(array($fieldName => $value));

        if (!$snapshot) {
            throw new \RuntimeException(sprintf('Unable to find the snapshot : %s', $value));
        }

        $page = new SnapshotPageProxy($this->getPageManager(), $snapshot);

        if ($page) {
           $this->loadBlocks($page);
        }

        return $page;
    }

    /**
     * load the blocks of the $page
     *
     * @param \Sonata\PageBundle\Model\PageInterface $page
     */
    public function loadBlocks(PageInterface $page)
    {
        $i = new RecursiveBlockIterator($page->getBlocks());

        foreach ($i as $block) {
            $this->blocks[$block->getId()] = $block;
        }
    }

    /**
     * @param integer $id
     * @return array|null
     */
    public function getBlock($id)
    {
        if (isset($this->blocks[$id])) {
            return $this->blocks[$id];
        }

        return null;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }
}