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

use Sonata\CacheBundle\Cache\CacheElement;

use Sonata\PageBundle\Model\PageInterface;
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
     * @param $statusCode
     * @return boolean
     */
    function hasErrorCode($statusCode);

    /**
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param $statusCode
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getErrorCodePage(SiteInterface $site, $statusCode);

    /**
     * @param string $name
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param null|\Sonata\BlockBundle\Model\BlockInterface $parentContainer
     * @return bool|null|\Sonata\BlockBundle\Model\BlockInterface
     */
    function findContainer($name, PageInterface $page, BlockInterface $parentContainer = null);

    /**
     * Returns a fully loaded page ( + blocks ) from a route name
     *
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param string $slug
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getPageByUrl(SiteInterface $site, $slug);

    /**
     * Returns a fully loaded page ( + blocks ) from a route name
     *
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param string $routeName
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getPageByRouteName(SiteInterface $site, $routeName);

    /**
     * Returns a fully loaded page ( + blocks ) from a name
     *
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param string $name
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getPageByName(SiteInterface $site, $name);

    /**
     * Returns a fully loaded pag ( + blocks ) from a page id
     *
     * @param integer $id
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getPageById($id);

    /**
     *
     * @param integer $id
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getBlock($id);

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
     * Returns the list of loaded block from the current http request
     *
     * @return array
     */
    function getBlocks();

    /**
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param $page
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function getPage(SiteInterface $site, $page);
}