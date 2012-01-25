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

use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\PageBundle\Model\SiteInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface CmsManagerInterface
{
    /**
     * Returns http error codes
     *
     * @return array
     */
    function getHttpErrorCodes();

    /**
     * @param string $name
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param null|\Sonata\PageBundle\Model\BlockInterface $parentContainer
     * @return bool|null|\Sonata\PageBundle\Model\BlockInterface
     */
    function findContainer($name, PageInterface $page, BlockInterface $parentContainer = null);

    /**
     * Returns a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param string $slug
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getPageByUrl(SiteInterface $site, $slug);

    /**
     * Returns a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param string $routeName
     * @param boolean $create
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getPageByRouteName(SiteInterface $site, $routeName, $create = true);

    /**
     * Returns a fully loaded page ( + blocks ) from a name
     *
     * if the page does not exists then the page is created.
     *
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param string $name
     * @param boolean $create
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getPageByName(SiteInterface $site, $name, $create = true);

    /**
     * Returns a fully loaded pag ( + blocks ) from a page id
     *
     * @abstract
     * @param integer $id
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getPageById($id);

    /**
     *
     * @abstract
     * @param integer $id
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getBlock($id);

    /**
     * @return string
     */
    function getCode();

    /**
     * @abstract
     * @param \Sonata\PageBundle\Cache\CacheElement $cacheElement
     * @return void
     */
    function invalidate(CacheElement $cacheElement);

    /**
     * @abstract
     * @return Symfony\Component\Routing\RouterInterface
     */
    function getRouter();

    /**
     * Returns the current page
     *
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getCurrentPage();

    /**
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return void
     */
    function setCurrentPage(PageInterface $page);

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     */
    function getBlockService(BlockInterface $block);

    /**
     * @param array $blockServices
     * @return void
     */
    function setBlockServices(array $blockServices);

    /**
     * @return array
     */
    function getBlockServices();

    /**
     * Returns the list of loaded block from the current http request
     *
     * @return array
     */
    function getBlocks();

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $requestType
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return boolean
     */
    function isDecorable(Request $request, $requestType, Response $response);

    /**
     * @abstract
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param array $params
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return void
     */
    function renderPage(PageInterface $page, array $params = array(), Response $response = null);
}