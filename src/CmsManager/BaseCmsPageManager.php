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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseCmsPageManager implements CmsManagerInterface
{
    protected ?PageInterface $currentPage = null;

    /**
     * @var array<PageBlockInterface|null>
     */
    protected array $blocks = [];

    public function getCurrentPage(): ?PageInterface
    {
        return $this->currentPage;
    }

    public function setCurrentPage(PageInterface $page): void
    {
        $this->currentPage = $page;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function getPageByUrl(SiteInterface $site, string $slug): PageInterface
    {
        return $this->getPageBy($site, 'url', $slug);
    }

    public function getPageByRouteName(SiteInterface $site, string $routeName): PageInterface
    {
        return $this->getPageBy($site, 'routeName', $routeName);
    }

    public function getPageByPageAlias(SiteInterface $site, string $pageAlias): PageInterface
    {
        return $this->getPageBy($site, 'pageAlias', $pageAlias);
    }

    public function getPageByName(SiteInterface $site, string $name): PageInterface
    {
        return $this->getPageBy($site, 'name', $name);
    }

    public function getPageById($id): PageInterface
    {
        return $this->getPageBy(null, 'id', $id);
    }

    /**
     * @param int|string $value
     *
     * @phpstan-param 'id'|'url'|'routeName'|'pageAlias'|'name' $fieldName
     */
    abstract protected function getPageBy(?SiteInterface $site, string $fieldName, $value): PageInterface;
}
