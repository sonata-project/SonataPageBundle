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
    public function findContainer(string $name, PageInterface $page, ?PageBlockInterface $parentContainer = null): ?PageBlockInterface;

    public function getPageByUrl(SiteInterface $site, string $slug): PageInterface;

    public function getPageByRouteName(SiteInterface $site, string $routeName): PageInterface;

    public function getPageByPageAlias(SiteInterface $site, string $pageAlias): PageInterface;

    public function getInternalRoute(SiteInterface $site, string $routeName): PageInterface;

    public function getPageByName(SiteInterface $site, string $name): PageInterface;

    /**
     * @param int|string $id
     */
    public function getPageById($id): PageInterface;

    /**
     * @param int|string $id
     */
    public function getBlock($id): ?PageBlockInterface;

    public function getCurrentPage(): ?PageInterface;

    public function setCurrentPage(PageInterface $page): void;

    /**
     * @return array<PageBlockInterface|null>
     */
    public function getBlocks(): array;

    /**
     * @param int|string|null $page
     */
    public function getPage(SiteInterface $site, $page): PageInterface;
}
