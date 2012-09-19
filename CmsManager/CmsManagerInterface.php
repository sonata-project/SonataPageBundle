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
 * The CmsManagerInterface class is in charge of retrieving the correct page (cms page or action page)
 *
 * An action page is linked to a symfony action and a cms page is a standalone page.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface CmsManagerInterface
{
    /**
     * @param string              $name
     * @param PageInterface       $page
     * @param null|BlockInterface $parentContainer
     *
     * @return bool|null|BlockInterface
     */
    function findContainer($name, PageInterface $page, BlockInterface $parentContainer = null);

    /**
     * Returns a fully loaded page ( + blocks ) from a url
     *
     * @param SiteInterface $site
     * @param string                                 $slug
     *
     * @return PageInterface
     */
    function getPageByUrl(SiteInterface $site, $slug);

    /**
     * Returns a fully loaded page ( + blocks ) from a route name
     *
     * @param SiteInterface $site
     * @param string        $routeName
     *
     * @return PageInterface
     */
    function getPageByRouteName(SiteInterface $site, $routeName);

    /**
     * Returns a fully loaded page ( + blocks ) from a route name
     *
     * @param SiteInterface $site
     * @param string        $pageAlias
     *
     * @return PageInterface
     */
    function getPageByPageAlias(SiteInterface $site, $pageAlias);

    /**
     * Returns a fully loaded page ( + blocks ) from an internal page name
     *
     * @param SiteInterface $site
     * @param string        $routeName
     *
     * @return string
     */
    function getInternalRoute(SiteInterface $site, $routeName);

    /**
     * Returns a fully loaded page ( + blocks ) from a name
     *
     * @param SiteInterface $site
     * @param string                                 $name
     *
     * @return PageInterface
     */
    function getPageByName(SiteInterface $site, $name);

    /**
     * Returns a fully loaded pag ( + blocks ) from a page id
     *
     * @param integer $id
     *
     * @return PageInterface
     */
    function getPageById($id);

    /**
     *
     * @param integer $id
     *
     * @return PageInterface
     */
    function getBlock($id);

    /**
     * Returns the current page
     *
     * @return PageInterface
     */
    function getCurrentPage();

    /**
     * @param PageInterface $page
     */
    function setCurrentPage(PageInterface $page);

    /**
     * Returns the list of loaded block from the current http request
     *
     * @return array
     */
    function getBlocks();

    /**
     * @param SiteInterface $site
     * @param string        $page
     *
     * @return PageInterface
     */
    function getPage(SiteInterface $site, $page);
}