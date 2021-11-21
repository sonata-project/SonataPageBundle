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
     * @var bool
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
     * @var PageInterface|null
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
     * @deprecated since sonata-project/page-bundle 2.4 and will be removed in 4.0
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
     * @var SiteInterface|null
     */
    protected $site;

    public function __toString()
    {
        return $this->getName() ?: '-';
    }

    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    public function getRouteName()
    {
        return $this->routeName;
    }

    public function setPageAlias($pageAlias)
    {
        $this->pageAlias = $pageAlias;
    }

    public function getPageAlias()
    {
        return $this->pageAlias;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPublicationDateStart(?\DateTime $publicationDateStart = null)
    {
        $this->publicationDateStart = $publicationDateStart;
    }

    public function getPublicationDateStart()
    {
        return $this->publicationDateStart;
    }

    public function setPublicationDateEnd(?\DateTime $publicationDateEnd = null)
    {
        $this->publicationDateEnd = $publicationDateEnd;
    }

    public function getPublicationDateEnd()
    {
        return $this->publicationDateEnd;
    }

    public function setCreatedAt(?\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setDecorate($decorate)
    {
        $this->decorate = $decorate;
    }

    public function getDecorate()
    {
        return $this->decorate;
    }

    public function isHybrid()
    {
        return PageInterface::PAGE_ROUTE_CMS_NAME !== $this->getRouteName();
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setPage(?PageInterface $page = null)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @deprecated since sonata-project/page-bundle 2.4 and will be removed in 4.0
     */
    public function setSources($sources)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 2.4 and will be removed in 3.0.', \E_USER_DEPRECATED);

        $this->sources = $sources;
    }

    /**
     * @deprecated since sonata-project/page-bundle 2.4 and will be removed in 4.0
     */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 2.4 and will be removed in 3.0.', \E_USER_DEPRECATED);

        return $this->sources;
    }

    public function setTarget($target)
    {
        $this->target = $target;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
    }

    public function getTargetId()
    {
        return $this->targetId;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setSite(?SiteInterface $site = null)
    {
        $this->site = $site;
    }

    public function getSite()
    {
        return $this->site;
    }
}
