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

use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;

/**
 * Page
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Page implements PageInterface
{
    protected $createdAt;

    protected $updatedAt;

    protected $routeName;

    protected $pageAlias;

    protected $type;

    protected $name;

    protected $title;

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

    protected $sources;

    protected $parent;

    protected $parents;

    protected $target;

    protected $children;

    protected $snapshots;

    protected $templateCode;

    protected $position = 1;

    protected $decorate = true;

    protected $site;

    protected $edited;

    protected static $slugifyMethod;

    /**
     * @return mixed
     */
    public static function getSlugifyMethod()
    {
        return self::$slugifyMethod;
    }

    /**
     * @param mixed $slugifyMethod
     */
    public static function setSlugifyMethod(\Closure $slugifyMethod)
    {
        self::$slugifyMethod = $slugifyMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->blocks        = array();
        $this->children      = array();
        $this->routeName     = PageInterface::PAGE_ROUTE_CMS_NAME;
        $this->requestMethod = 'GET|POST|HEAD|DELETE|PUT';
        $this->edited        = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;
    }

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
        if (substr($pageAlias, 0, 12) != '_page_alias_') {
            $pageAlias = '_page_alias_'.$pageAlias;
        }

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
    public function setSlug($slug)
    {
        $this->slug = self::slugify(trim($slug));
    }

    /**
     * {@inheritdoc}
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomUrl($customUrl)
    {
        $this->customUrl = $customUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomUrl()
    {
        return $this->customUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaKeyword($metaKeyword)
    {
        $this->metaKeyword = $metaKeyword;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaKeyword()
    {
        return $this->metaKeyword;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function setJavascript($javascript)
    {
        $this->javascript = $javascript;
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascript()
    {
        return $this->javascript;
    }

    /**
     * {@inheritdoc}
     */
    public function setStylesheet($stylesheet)
    {
        $this->stylesheet = $stylesheet;
    }

    /**
     * {@inheritdoc}
     */
    public function getStylesheet()
    {
        return $this->stylesheet;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawHeaders($rawHeaders)
    {
        $headers = $this->getHeadersAsArray($rawHeaders);

        $this->setHeaders($headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawHeaders()
    {
        return $this->rawHeaders;
    }

    /**
     * {@inheritdoc}
     */
    public function addHeader($name, $header)
    {
        $headers = $this->getHeaders();

        $headers[$name] = $header;

        $this->headers = $headers;

        $this->rawHeaders = $this->getHeadersAsString($headers);
    }

    /**
     * {@inheritdoc}
     */
    public function setHeaders(array $headers = array())
    {
        $this->headers = array();
        $this->rawHeaders = null;
        foreach ($headers as $name => $header) {
            $this->addHeader($name, $header);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        if (null === $this->headers) {
            $rawHeaders = $this->getRawHeaders();
            $this->headers = $this->getHeadersAsArray($rawHeaders);
        }

        return $this->headers;
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
    public function addChildren(PageInterface $children)
    {
        $this->children[] = $children;

        $children->setParent($this);
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
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * {@inheritdoc}
     */
    public function getSnapshot()
    {
        return $this->snapshots && $this->snapshots[0] ? $this->snapshots[0] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSnapshots()
    {
        return $this->snapshots;
    }

    /**
     * {@inheritdoc}
     */
    public function setSnapshots($snapshots)
    {
        $this->snapshots = $snapshots;
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
     * @param PageBlockInterface $blocs
     */
    public function addBlocks(PageBlockInterface $blocs)
    {
        $blocs->setPage($this);

        $this->blocks[] = $blocs;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(PageInterface $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function setParents(array $parents)
    {
        $this->parents = $parents;
    }

    /**
     * {@inheritdoc}
     */
    public function getParents()
    {
        if (!$this->parents) {

            $page    = $this;
            $parents = array();

            while ($page->getParent()) {
                $page      = $page->getParent();
                $parents[] = $page;
            }

            $this->setParents(array_reverse($parents));
        }

        return $this->parents;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplateCode($templateCode)
    {
        $this->templateCode = $templateCode;
    }

    /**
     * {@inheritdoc}
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
        return $this->getRouteName() != self::PAGE_ROUTE_CMS_NAME  && !$this->isInternal();
    }

    /**
     * {@inheritdoc}
     */
    public function isCms()
    {
        return $this->getRouteName() == self::PAGE_ROUTE_CMS_NAME && !$this->isInternal();
    }

    /**
     * {@inheritdoc}
     */
    public function isInternal()
    {
        return substr($this->getRouteName(), 0, 15) == '_page_internal_';
    }

    /**
     * {@inheritdoc}
     */
    public function isDynamic()
    {
        return $this->isHybrid() && strpos($this->getUrl(), '{') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function isError()
    {
        return substr($this->getRouteName(), 0, 21) == '_page_internal_error_';
    }


    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName() ?: '-';
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
     * source : http://snipplr.com/view/22741/slugify-a-string-in-php/
     *
     * @static
     *
     * @param string $text
     *
     * @return mixed|string
     */
    public static function slugify($text)
    {
        // this code is for BC
        if (!self::$slugifyMethod) {
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

            return $text;
        }

        return call_user_func(self::$slugifyMethod, $text);
    }

    /**
     * Retrieve a block by code
     *
     * @param string $code
     *
     * @return PageBlockInterface
     */
    public function getContainerByCode($code)
    {
        $block = null;

        foreach ($this->getBlocks() as $blockTmp) {
            if (in_array($blockTmp->getType(), array('sonata.page.block.container', 'sonata.block.service.container')) && $blockTmp->getSetting('code') == $code) {
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
     *
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
     * {@inheritdoc}
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

    /**
     * {@inheritdoc}
     */
    public function setEdited($edited)
    {
        $this->edited = $edited;
    }

    /**
     * {@inheritdoc}
     */
    public function getEdited()
    {
        return $this->edited;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Converts the headers passed as string to an array
     *
     * @param string $rawHeaders The headers
     *
     * @return array
     */
    protected function getHeadersAsArray($rawHeaders)
    {
        $headers = array();

        foreach (explode("\r\n", $rawHeaders) as $header) {
            if (false != strpos($header, ':')) {
                list($name, $headerStr) = explode(':', $header, 2);
                $headers[trim($name)] = trim($headerStr);
            }
        }

        return $headers;
    }

    /**
     * Converts the headers passed as an associative array to a string
     *
     * @param array $headers The headers
     *
     * @return string
     */
    protected function getHeadersAsString(array $headers)
    {
        $rawHeaders = array();

        foreach ($headers as $name => $header) {
            $rawHeaders[] = sprintf('%s: %s', trim($name), trim($header));
        }

        $rawHeaders = implode("\r\n", $rawHeaders);

        return $rawHeaders;
    }
}
