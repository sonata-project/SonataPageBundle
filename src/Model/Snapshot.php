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

namespace Sonata\PageBundle\Model;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Snapshot implements SnapshotInterface
{
    /**
     * @var int|string|null
     */
    protected $id = null;

    /**
     * @var string|null
     */
    protected $routeName = null;

    /**
     * @var string|null
     */
    protected $pageAlias = null;

    /**
     * @var string|null
     */
    protected $type = null;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var string|null
     */
    protected $name = null;

    /**
     * @var string|null
     */
    protected $url = null;

    /**
     * @var \DateTimeInterface|null
     */
    protected $publicationDateStart = null;

    /**
     * @var \DateTimeInterface|null
     */
    protected $publicationDateEnd = null;

    /**
     * @var \DateTimeInterface|null
     */
    protected $createdAt = null;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt = null;

    /**
     * @var bool
     */
    protected $decorate = true;

    /**
     * @var int
     */
    protected $position = 1;

    /**
     * @var PageInterface|null
     */
    protected $page = null;

    /**
     * @var SiteInterface|null
     */
    protected $site = null;

    /**
     * @var array
     */
    protected $content = [];

    /**
     * @var array<PageInterface>
     */
    protected $children = [];

    /**
     * @var int|string|null
     */
    protected $parentId = null;

    public function __toString()
    {
        return $this->getName() ?? '-';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function setRouteName($routeName): void
    {
        $this->routeName = $routeName;
    }

    public function getPageAlias()
    {
        return $this->pageAlias;
    }

    public function setPageAlias($pageAlias): void
    {
        $this->pageAlias = $pageAlias;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    public function getPublicationDateStart()
    {
        return $this->publicationDateStart;
    }

    public function setPublicationDateStart(?\DateTimeInterface $publicationDateStart = null): void
    {
        $this->publicationDateStart = $publicationDateStart;
    }

    public function getPublicationDateEnd()
    {
        return $this->publicationDateEnd;
    }

    public function setPublicationDateEnd(?\DateTimeInterface $publicationDateEnd = null): void
    {
        $this->publicationDateEnd = $publicationDateEnd;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getDecorate()
    {
        return $this->decorate;
    }

    public function setDecorate($decorate): void
    {
        $this->decorate = $decorate;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position): void
    {
        $this->position = $position;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage(?PageInterface $page = null): void
    {
        $this->page = $page;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setSite(?SiteInterface $site = null): void
    {
        $this->site = $site;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function setParentId($parentId): void
    {
        $this->parentId = $parentId;
    }

    public function isHybrid()
    {
        return PageInterface::PAGE_ROUTE_CMS_NAME !== $this->getRouteName();
    }
}
