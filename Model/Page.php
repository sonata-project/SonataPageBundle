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

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;

abstract class Page implements PageInterface
{
    protected $createdAt;

    protected $updatedAt;

    protected $routeName;

    protected $name;

    protected $slug;

    protected $url;

    protected $customUrl;

    protected $requestMethod;

    protected $metaKeyword;

    protected $metaDescription;

    protected $javascript;

    protected $stylesheet;

    protected $rawHeaders;

    protected $headers;

    protected $enabled;

    protected $blocks;

    protected $parent;

    protected $parents;

    protected $target;

    protected $sources;

    protected $children;

    protected $snapshots;

    protected $templateCode;

    protected $position = 1;

    protected $decorate = true;

    protected $ttl;

    protected $site;

    protected $edited;

    public function __construct()
    {
        $this->blocks        = array();
        $this->children      = array();
        $this->routeName     = PageInterface::PAGE_ROUTE_CMS_NAME;
        $this->requestMethod = 'GET|POST|HEAD|DELETE|PUT';
        $this->edited        = true;
    }

    /**
     * Set routeName
     *
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get routeName
     *
     * @return string $routeName
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param integer $slug
     */
    public function setSlug($slug)
    {
        $this->slug = self::slugify(trim($slug));
    }

    /**
     * Get slug
     *
     * @return integer $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set customUrl
     *
     * @param integer $customUrl
     */
    public function setCustomUrl($customUrl)
    {
        $this->customUrl = $customUrl;
    }

    /**
     * Get customUrl
     *
     * @return integer $customUrl
     */
    public function getCustomUrl()
    {
        return $this->customUrl;
    }

    /**
     * Set requestMethod
     *
     * @param string $requestMethod
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * Get requestMethod
     *
     * @return string $requestMethod
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Set metaKeyword
     *
     * @param string $metaKeyword
     */
    public function setMetaKeyword($metaKeyword)
    {
        $this->metaKeyword = $metaKeyword;
    }

    /**
     * Get metaKeyword
     *
     * @return string $metaKeyword
     */
    public function getMetaKeyword()
    {
        return $this->metaKeyword;
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * Get metaDescription
     *
     * @return string $metaDescription
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set javascript
     *
     * @param text $javascript
     */
    public function setJavascript($javascript)
    {
        $this->javascript = $javascript;
    }

    /**
     * Get javascript
     *
     * @return text $javascript
     */
    public function getJavascript()
    {
        return $this->javascript;
    }

    /**
     * Set stylesheet
     *
     * @param text $stylesheet
     */
    public function setStylesheet($stylesheet)
    {
        $this->stylesheet = $stylesheet;
    }

    /**
     * Get stylesheet
     *
     * @return text $stylesheet
     */
    public function getStylesheet()
    {
        return $this->stylesheet;
    }

    /**
     * Set raw headers
     *
     * @param text $rawHeaders
     */
    public function setRawHeaders($rawHeaders)
    {
        $headers = array();

        foreach (explode("\r\n", $rawHeaders) as $header) {
            if (false != strpos($header, ':')) {
                list($name, $headerStr) = explode(':', $header, 2);
                $headers[trim($name)] = trim($headerStr);
            }
        }

        $this->setHeaders($headers);
    }

    /**
     * Get Raw headers
     *
     * @return text $rawHeaders
     */
    public function getRawHeaders()
    {
        return $this->rawHeaders;
    }

    /**
     * Add header
     *
     * @param text $headers
     */
    public function addHeader($name, $header)
    {
        $headers = $this->getHeaders();

        $headers[$name] = $header;

        $this->headers = $headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers = array())
    {
        $this->headers = $headers;
    }

    /**
     * Get headers
     *
     * @return array $headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add children
     *
     * @param \Sonata\PageBundle\Model\PageInterface $children
     */
    public function addChildren(PageInterface $children)
    {
        $this->children[] = $children;

        $children->setParent($this);
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection $children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * Get snapshot
     *
     * @return  Application\Sonata\PageBundle\Entity\Snapshot $snapshots
     */
    public function getSnapshot()
    {
        return $this->snapshots && $this->snapshots[0] ? $this->snapshots[0] : null;
    }

    /**
     * Get snapshots
     *
     * @return Doctrine\Common\Collections\Collection $snapshots
     */
    public function getSnapshots()
    {
        return $this->snapshots;
    }

    /**
     * Set $snapshots
     *
     * @param Doctrine\Common\Collections\Collection $snapshots
     */
    public function setSnapshots($snapshots)
    {
        $this->snapshots = $snapshots;
    }

    /**
     * Get target
     *
     * @return Application\Sonata\PageBundle\Entity\Page $target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Add snapshot
     *
     * @param \Sonata\PageBundle\Model\SnapshotInterface $snapshot
     */
    public function addSnapshot(SnapshotInterface $snapshot)
    {
        $this->snapshots[] = $snapshot;

        $snapshot->setPage($this);
    }

    /**
     * Set target
     *
     * @param \Sonata\PageBundle\Model\PageInterface $target
     */
    public function setTarget(PageInterface $target = null)
    {
        $this->target = $target;
    }

    /**
     * Add blocs
     *
     * @param \Sonata\BlockBundle\Model\BlockInterface $blocs
     */
    public function addBlocks(BlockInterface $blocs)
    {
        $this->blocks[] = $blocs;
    }

    /**
     * Get blocs
     *
     * @return Doctrine\Common\Collections\Collection $blocs
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * Set parent
     *
     * @param \Sonata\PageBundle\Model\PageInterface $parent
     */
    public function setParent(PageInterface $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @param integer $level default -1
     * @return \Sonata\PageBundle\Model\PageInterface $parent
     */
    public function getParent($level = -1)
    {
        if (-1 === $level) {
           return $this->parent;
        }

        $parents = $this->getParents();

        if ($level < 0) {
            $level = count($parents) + $level;
        }

        return isset($parents[$level]) ? $parents[$level] : null;
    }

    /**
     * Set the parent tree
     *
     * @param array $parents
     */
    public function setParents(array $parents)
    {
        $this->parents = $parents;
    }

    /**
     * get the tree of the page, build it from the parent if the tree does not exist
     *
     * @return array of \Sonata\PageBundle\Model\PageInterface $parents
     */
    public function getParents()
    {
        if (!$this->parents) {

            $page = $this;
            $parents = array();

            while ($page->getParent()) {
                $page = $page->getParent();
                $parents[] = $page;
            }

            $this->setParents(array_reverse($parents));
        }

        return $this->parents;
    }

    /**
     * Set template
     *
     * @param string $templateCode
     */
    public function setTemplateCode($templateCode)
    {
        $this->templateCode = $templateCode;
    }

    /**
     * Get template
     *
     * @return string $templateCode
     */
    public function getTemplateCode()
    {
        return $this->templateCode;
    }

    public function disableBlockLazyLoading()
    {
        if (is_object($this->blocks)) {
            $this->blocks->setInitialized(true);
        }
    }

    public function disableChildrenLazyLoading()
    {
        if (is_object($this->children)) {
            $this->children->setInitialized(true);
        }
    }

    public function setDecorate($decorate)
    {
        $this->decorate = $decorate;
    }

    public function getDecorate()
    {
        return $this->decorate;
    }

    /**
     * Returns true if the page represents an action
     *
     * @return boolean
     */
    public function isHybrid()
    {
        return $this->getRouteName() != self::PAGE_ROUTE_CMS_NAME;
    }

    /**
     * @return bool
     */
    public function isCms()
    {
        return !$this->isHybrid();
    }

    /**
     * Returns true if the page is internal, ie: no direct url
     * @return boolean
     */
    public function isInternal()
    {
        return null === $this->getUrl() && !$this->isCms() && !$this->isHybrid();
    }

    /**
     * Return true if the page is dynamic, ie hybrid and contains dynamic parameters
     *
     * @return bool
     */
    public function isDynamic()
    {
        return $this->isHybrid() && strpos($this->getUrl(), '{') !== false;
    }

    public function __toString()
    {
        return $this->getName()?: '-';
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getTtl()
    {
        if ($this->ttl === null) {
            $ttl = 84600 * 10; // todo : change this value

            foreach ($this->getBlocks() as $block) {
                $blockTtl = $block->getTtl();

                $ttl = ($blockTtl < $ttl) ? $blockTtl : $ttl ;
            }

            $this->ttl = $ttl;
        }

        return $this->ttl;
    }

    public function setUrl($url)
    {
        $this->url = $this->routeName == 'homepage' ? '/' : $url;
    }

    public function getUrl()
    {
        return $this->routeName == 'homepage' ? '/' : $this->url;
    }

    /**
     * source : http://snipplr.com/view/22741/slugify-a-string-in-php/
     *
     * @static
     * @param  $text
     * @return mixed|string
     */
    static public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * Retrieve a block by name
     *
     * @param string $name
     * @return Sonata\BlockBundle\Model\BlockInterface
     */
    public function getContainerByName($name)
    {
        $block = null;

        foreach ($this->getBlocks() as $blockTmp) {
            if ($blockTmp->getType() == 'sonata.page.block.container' && $name == $blockTmp->getSetting('name')) {
                $block = $blockTmp;

                break;
            }
        }

        return $block;
    }

    /**
     * Retrieve blocks by type
     *
     * @param string $type
     * @return array
     */
    public function getBlocksByType($type)
    {
        $blocks = array();

        foreach ($this->getBlocks() as $block) {
            if ($type == $block->getType()) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    /**
     * Return true if the page has the request method $method
     * @param string $method
     *
     * @return bool
     */
    public function hasRequestMethod($method)
    {
        $method = strtoupper($method);

        if (!in_array($method, array('PUT', 'POST', 'GET', 'DELETE', 'HEAD'))) {
            return false;
        }

        return !$this->getRequestMethod() || false !== strpos($this->getRequestMethod(), $method);
    }

    /**
     * @param SiteInterface $site
     * @return void
     */
    public function setSite(SiteInterface $site)
    {
        $this->site = $site;
    }

    /**
     * @return
     */
    public function getSite()
    {
        return $this->site;
    }

    public function setEdited($edited)
    {
        $this->edited = $edited;
    }

    public function getEdited()
    {
        return $this->edited;
    }
}