<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Model;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;

/**
 * Snapshot
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Snapshot implements SnapshotInterface
{
    protected $createdAt;

    protected $updatedAt;

    protected $routeName;

    protected $pageAlias;

    protected $type;

    protected $name;

    protected $url;

    protected $enabled;

    protected $publicationDateStart;

    protected $publicationDateEnd;

    protected $position = 1;

    protected $decorate = true;

    protected $content = array();

    protected $page;

    protected $children = array();

    protected $parent;

    protected $parentId;

    protected $sources;

    protected $target;

    protected $targetId;

    protected $site;

    /**
     * {@inheritdoc}
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageAlias($pageAlias)
    {
        $this->pageAlias = $pageAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageAlias()
    {
        return $this->pageAlias;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublicationDateStart(\DateTime $publicationDateStart = null)
    {
        $this->publicationDateStart = $publicationDateStart;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicationDateStart()
    {
        return $this->publicationDateStart;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublicationDateEnd(\DateTime $publicationDateEnd = null)
    {
        $this->publicationDateEnd = $publicationDateEnd;
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicationDateEnd()
    {
        return $this->publicationDateEnd;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorate($decorate)
    {
        $this->decorate = $decorate;
    }

    /**
     * {@inheritdoc}
     */
    public function getDecorate()
    {
        return $this->decorate;
    }

    /**
     * {@inheritdoc}
     */
    public function isHybrid()
    {
        return $this->getRouteName() != self::PAGE_ROUTE_CMS_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName()?: '-';
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setPage(PageInterface $page = null)
    {
        $this->page = $page;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * {@inheritdoc}
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * {@inheritdoc}
     */
    public function setSources($sources)
    {
        $this->sources = $sources;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->sources;
    }

    /**
     * {@inheritdoc}
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * {@inheritdoc}
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
    }

    /**
     * {@inheritdoc}
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * {@inheritdoc}
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function setSite(SiteInterface $site)
    {
        $this->site = $site;
    }

    /**
     * {@inheritdoc}
     */
    public function getSite()
    {
        return $this->site;
    }
}
