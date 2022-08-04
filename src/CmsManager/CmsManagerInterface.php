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
     * @param string $slug
     */
    public function getPageByUrl(SiteInterface $site, $slug): PageInterface;

    /**
     * @param string $routeName
     *
     * @return PageInterface
     */
    public function getPageByRouteName(SiteInterface $site, $routeName);

    /**
     * @param string $pageAlias
     *
     * @return PageInterface
     */
    public function getPageByPageAlias(SiteInterface $site, $pageAlias);

    /**
     * @param string $routeName
     *
     * @return PageInterface
     */
    public function getInternalRoute(SiteInterface $site, $routeName);

    /**
     * @param string $name
     *
     * @return PageInterface
     */
    public function getPageByName(SiteInterface $site, $name);

    /**
     * @param int|string $id
     *
     * @return PageInterface
     */
    public function getPageById($id);

    /**
     * @param int|string $id
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

    /**
     * @return void
     */
    public function setCurrentPage(PageInterface $page);

    /**
     * @return array<PageBlockInterface|null>
     */
    public function getBlocks();

    /**
     * @param int|string|null $page
     *
     * @return PageInterface
     */
    public function getPage(SiteInterface $site, $page);
}
