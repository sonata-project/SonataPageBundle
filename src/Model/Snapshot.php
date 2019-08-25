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
 * Snapshot.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Snapshot implements SnapshotInterface
{
    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var string|null
     */
    protected $pageAlias;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var
     */
    protected $enabled;

    /**
     * @var \DateTime|null
     */
    protected $publicationDateStart;

    /**
     * @var \DateTime|null
     */
    protected $publicationDateEnd;

    /**
     * @var int
     */
    protected $position = 1;

    /**
     * @var bool
     */
    protected $decorate = true;

    /**
     * @var array
     */
    protected $content = [];

    /**
     * @var PageInterface
     */
    protected $page;

    /**
     * @var PageInterface[]
     */
    protected $children = [];

    /**
     * @var PageInterface
     */
    protected $parent;

    /**
     * @var int|null
     */
    protected $parentId;

    /**
     * @deprecated since version 2.4 and will be removed in 3.0
     */
    protected $sources;

    /**
     * @var PageInterface|null
     */
    protected $target;

    /**
     * @var int|null
     */
    protected $targetId;

    /**
     * @var SiteInterface
     */
    protected $site;

    public function __toString()
    {
        return $this->getName() ?: '-';
    }

    public function setRouteName($routeName): void
    {
        $this->routeName = $routeName;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function setPageAlias($pageAlias): void
    {
        $this->pageAlias = $pageAlias;
    }

    public function getPageAlias()
    {
        return $this->pageAlias;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPublicationDateStart(\DateTime $publicationDateStart = null): void
    {
        $this->publicationDateStart = $publicationDateStart;
    }

    public function getPublicationDateStart()
    {
        return $this->publicationDateStart;
    }

    public function setPublicationDateEnd(\DateTime $publicationDateEnd = null): void
    {
        $this->publicationDateEnd = $publicationDateEnd;
    }

    public function getPublicationDateEnd()
    {
        return $this->publicationDateEnd;
    }

    public function setCreatedAt(\DateTime $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setDecorate($decorate): void
    {
        $this->decorate = $decorate;
    }

    public function getDecorate()
    {
        return $this->decorate;
    }

    public function isHybrid()
    {
        return self::PAGE_ROUTE_CMS_NAME !== $this->getRouteName();
    }

    public function setPosition($position): void
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setContent($content): void
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setPage(PageInterface $page = null): void
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setChildren($children): void
    {
        $this->children = $children;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setParent($parent): void
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParentId($parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @deprecated since version 2.4 and will be removed in 3.0
     */
    public function setSources($sources): void
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 2.4 and will be removed in 3.0.', E_USER_DEPRECATED);

        $this->sources = $sources;
    }

    /**
     * @deprecated since version 2.4 and will be removed in 3.0
     */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 2.4 and will be removed in 3.0.', E_USER_DEPRECATED);

        return $this->sources;
    }

    public function setTarget($target): void
    {
        $this->target = $target;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTargetId($targetId): void
    {
        $this->targetId = $targetId;
    }

    public function getTargetId()
    {
        return $this->targetId;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setSite(SiteInterface $site): void
    {
        $this->site = $site;
    }

    public function getSite()
    {
        return $this->site;
    }
}
