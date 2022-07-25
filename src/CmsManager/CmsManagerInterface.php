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

use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;

/**
 * The CmsManagerInterface class is in charge of retrieving the correct page (cms page or action page).
 *
 * An action page is linked to a symfony action and a cms page is a standalone page.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface CmsManagerInterface
{
    /**
     * @param string $name
     *
     * @return PageBlockInterface|null
     */
    public function findContainer($name, PageInterface $page, ?PageBlockInterface $parentContainer = null);

    /**
     * Returns a fully loaded page ( + blocks ) from a url.
     *
     * @param string $slug
     */
    public function getPageByUrl(SiteInterface $site, $slug): PageInterface;

    /**
     * Returns a fully loaded page ( + blocks ) from a route name.
     *
     * @param string $routeName
     *
     * @return PageInterface
     */
    public function getPageByRouteName(SiteInterface $site, $routeName);

    /**
     * Returns a fully loaded page ( + blocks ) from a page alias.
     *
     * @param string $pageAlias
     *
     * @return PageInterface
     */
    public function getPageByPageAlias(SiteInterface $site, $pageAlias);

    /**
     * Returns a fully loaded page ( + blocks ) from an internal route name.
     *
     * @param string $routeName
     *
     * @return PageInterface
     */
    public function getInternalRoute(SiteInterface $site, $routeName);

    /**
     * Returns a fully loaded page ( + blocks ) from a page name.
     *
     * @param string $name
     *
     * @return PageInterface
     */
    public function getPageByName(SiteInterface $site, $name);

    /**
     * Returns a fully loaded pag ( + blocks ) from a page id.
     *
     * @param int $id
     *
     * @return PageInterface
     */
    public function getPageById($id);

    /**
     * @param int $id
     *
     * @return PageBlockInterface|null
     */
    public function getBlock($id);

    /**
     * Returns the current page.
     *
     * @return PageInterface|null
     */
    public function getCurrentPage();

    public function setCurrentPage(PageInterface $page);

    /**
     * Returns the list of loaded block from the current http request.
     *
     * @return PageBlockInterface[]
     */
    public function getBlocks();

    /**
     * @param int|string|null $page
     *
     * @return PageInterface
     */
    public function getPage(SiteInterface $site, $page);
}
