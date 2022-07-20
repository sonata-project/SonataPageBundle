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
 * Base class CMS Manager.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseCmsPageManager implements CmsManagerInterface
{
    /**
     * @var PageInterface
     */
    protected $currentPage;

    /**
     * @var PageBlockInterface[]
     */
    protected $blocks = [];

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function setCurrentPage(PageInterface $page): void
    {
        $this->currentPage = $page;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function getPageByUrl(SiteInterface $site, $slug): PageInterface
    {
        return $this->getPageBy($site, 'url', $slug);
    }

    public function getPageByRouteName(SiteInterface $site, $routeName)
    {
        return $this->getPageBy($site, 'routeName', $routeName);
    }

    public function getPageByPageAlias(SiteInterface $site, $pageAlias)
    {
        return $this->getPageBy($site, 'pageAlias', $pageAlias);
    }

    public function getPageByName(SiteInterface $site, $name)
    {
        return $this->getPageBy($site, 'name', $name);
    }

    public function getPageById($id)
    {
        return $this->getPageBy(null, 'id', $id);
    }

    /**
     * @param string $fieldName
     *
     * @return PageInterface
     */
    abstract protected function getPageBy(?SiteInterface $site, $fieldName, $value);
}
