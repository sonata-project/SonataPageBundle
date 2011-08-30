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

/**
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface CmsManagerInterface
{
    /**
     * @param string $name
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param null|\Sonata\PageBundle\Model\BlockInterface $parentContainer
     * @return bool|null|\Sonata\PageBundle\Model\BlockInterface
     */
    public function findContainer($name, PageInterface $page, BlockInterface $parentContainer = null);

    /**
     * Returns a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param string $slug
     * @return Application\Sonata\PageBundle\Model\PageInterface
     */
    public function getPageByUrl($slug);

    /**
     * Returns a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param string $routeName
     * @param boolean $create
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageByRouteName($routeName, $create = true);

    /**
     * Returns a fully loaded pag ( + blocks ) from a page id
     *
     * @abstract
     * @param integer $id
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageById($id);

    /**
     *
     * @abstract
     * @param integer $id
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getBlock($id);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @abstract
     * @param \Sonata\PageBundle\Cache\CacheElement $cacheElement
     * @return void
     */
    public function invalidate(CacheElement $cacheElement);

    /**
     * @abstract
     * @return Symfony\Component\Routing\RouterInterface
     */
    public function getRouter();
}