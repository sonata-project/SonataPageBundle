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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Page implements PageInterface
{
    /**
     * @var int|string|null
     */
    protected $id = null;

    /**
     * @var string|null
     */
    protected $title = null;

    /**
     * @var string|null
     */
    protected $routeName = PageInterface::PAGE_ROUTE_CMS_NAME;

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
    protected $slug = null;

    /**
     * @var string|null
     */
    protected $url = null;

    /**
     * @var string|null
     */
    protected $customUrl = null;

    /**
     * @var string|null
     */
    protected $metaKeyword = null;

    /**
     * @var string|null
     */
    protected $metaDescription = null;

    /**
     * @var string|null
     */
    protected $javascript = null;

    /**
     * @var string|null
     */
    protected $stylesheet = null;

    /**
     * @var \DateTimeInterface|null
     */
    protected $createdAt = null;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt = null;

    /**
     * @var Collection<array-key, PageInterface>
     */
    protected $children;

    /**
     * @var Collection<array-key, PageBlockInterface>
     */
    protected $blocks;

    /**
     * @var PageInterface|null
     */
    protected $parent = null;

    /**
     * @var array<PageInterface>|null
     */
    protected $parents = null;

    /**
     * @var string|null
     */
    protected $templateCode = null;

    /**
     * @var bool
     */
    protected $decorate = true;

    /**
     * @var int|null
     */
    protected $position = 1;

    /**
     * @var string|null
     */
    protected $requestMethod = 'GET|POST|HEAD|DELETE|PUT';

    /**
     * @var array<string, mixed>
     */
    protected $headers = [];

    /**
     * @var string|null
     */
    protected $rawHeaders = null;

    /**
     * @var SiteInterface|null
     */
    protected $site = null;

    /**
     * @var bool
     */
    protected $edited = true;

    /**
     * @var array<SnapshotInterface>
     */
    protected $snapshots = [];

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName() ?? '-';
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
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
        if ('_page_alias_' !== substr((string) $pageAlias, 0, 12)) {
            $pageAlias = '_page_alias_'.$pageAlias;
        }

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

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug): void
    {
        $this->slug = $slug;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    public function getCustomUrl()
    {
        return $this->customUrl;
    }

    public function setCustomUrl($customUrl): void
    {
        $this->customUrl = $customUrl;
    }

    public function getMetaKeyword()
    {
        return $this->metaKeyword;
    }

    public function setMetaKeyword($metaKeyword): void
    {
        $this->metaKeyword = $metaKeyword;
    }

    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    public function setMetaDescription($metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getJavascript()
    {
        return $this->javascript;
    }

    public function setJavascript($javascript): void
    {
        $this->javascript = $javascript;
    }

    public function getStylesheet()
    {
        return $this->stylesheet;
    }

    public function setStylesheet($stylesheet): void
    {
        $this->stylesheet = $stylesheet;
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

    public function getChildren()
    {
        return $this->children;
    }

    public function setChildren($children): void
    {
        $this->children = $children;
    }

    public function addChild(PageInterface $child): void
    {
        $this->children[] = $child;

        $child->setParent($this);
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function addBlock(PageBlockInterface $block): void
    {
        $block->setPage($this);

        $this->blocks[] = $block;
    }

    public function getContainerByCode($code)
    {
        foreach ($this->getBlocks() as $block) {
            if (\in_array($block->getType(), ['sonata.page.block.container', 'sonata.block.service.container'], true) && $block->getSetting('code') === $code) {
                return $block;
            }
        }

        return null;
    }

    public function getBlocksByType($type)
    {
        $blocks = [];

        foreach ($this->getBlocks() as $block) {
            if ($type === $block->getType()) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    public function getParent($level = -1)
    {
        if (-1 === $level) {
            return $this->parent;
        }

        $parents = $this->getParents();

        if ($level < 0) {
            $level = \count($parents) + $level;
        }

        return $parents[$level] ?? null;
    }

    public function setParent(?PageInterface $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParents()
    {
        if (!$this->parents) {
            $parent = $this->getParent();
            $parents = [];

            while (null !== $parent) {
                $parents[] = $parent;

                $parent = $parent->getParent();
            }

            $this->parents = array_reverse($parents);
        }

        return $this->parents;
    }

    public function setParents(array $parents): void
    {
        $this->parents = $parents;
    }

    public function getTemplateCode()
    {
        return $this->templateCode;
    }

    public function setTemplateCode($templateCode): void
    {
        $this->templateCode = $templateCode;
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

    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    public function setRequestMethod($method): void
    {
        $this->requestMethod = $method;
    }

    public function hasRequestMethod($method)
    {
        $method = strtoupper($method);

        if (!\in_array($method, ['PUT', 'POST', 'GET', 'DELETE', 'HEAD'], true)) {
            return false;
        }

        return !$this->getRequestMethod() || false !== strpos($this->getRequestMethod(), $method);
    }

    public function getHeaders(): array
    {
        if (null === $this->headers) {
            $rawHeaders = $this->getRawHeaders();

            $this->headers = $this->getHeadersAsArray($rawHeaders);
        }

        return $this->headers;
    }

    public function setHeaders(array $headers = []): void
    {
        $this->headers = [];
        $this->rawHeaders = null;
        foreach ($headers as $name => $header) {
            $this->addHeader($name, $header);
        }
    }

    public function addHeader($name, $value): void
    {
        $headers = $this->getHeaders();

        $headers[$name] = $value;

        $this->headers = $headers;

        $this->rawHeaders = $this->getHeadersAsString($headers);
    }

    public function getRawHeaders()
    {
        return $this->rawHeaders;
    }

    public function setRawHeaders($rawHeaders): void
    {
        $headers = $this->getHeadersAsArray($rawHeaders);

        $this->setHeaders($headers);
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setSite(?SiteInterface $site = null): void
    {
        $this->site = $site;
    }

    public function getEdited()
    {
        return $this->edited;
    }

    public function setEdited($edited): void
    {
        $this->edited = $edited;
    }

    public function getSnapshots()
    {
        return $this->snapshots;
    }

    public function setSnapshots($snapshots): void
    {
        $this->snapshots = $snapshots;
    }

    public function getSnapshot()
    {
        return $this->snapshots[0] ?? null;
    }

    public function addSnapshot(SnapshotInterface $snapshot): void
    {
        $this->snapshots[] = $snapshot;

        $snapshot->setPage($this);
    }

    public function isError()
    {
        return '_page_internal_error_' === substr($this->getRouteName() ?? '', 0, 21);
    }

    public function isHybrid()
    {
        return PageInterface::PAGE_ROUTE_CMS_NAME !== $this->getRouteName() && !$this->isInternal();
    }

    public function isDynamic()
    {
        return $this->isHybrid() && false !== strpos($this->getUrl() ?? '', '{');
    }

    public function isCms()
    {
        return PageInterface::PAGE_ROUTE_CMS_NAME === $this->getRouteName() && !$this->isInternal();
    }

    public function isInternal()
    {
        return '_page_internal_' === substr($this->getRouteName() ?? '', 0, 15);
    }

    /**
     * @param string|null $rawHeaders
     *
     * @return array<string, string>
     */
    private function getHeadersAsArray($rawHeaders)
    {
        $headers = [];

        foreach (explode("\r\n", (string) $rawHeaders) as $header) {
            if (false !== strpos($header, ':')) {
                [$name, $headerStr] = explode(':', $header, 2);
                $headers[trim($name)] = trim($headerStr);
            }
        }

        return $headers;
    }

    /**
     * @param array<string, mixed> $headers
     *
     * @return string
     */
    private function getHeadersAsString(array $headers)
    {
        $rawHeaders = [];

        foreach ($headers as $name => $header) {
            $rawHeaders[] = sprintf('%s: %s', trim($name), trim($header));
        }

        $rawHeaders = implode("\r\n", $rawHeaders);

        return $rawHeaders;
    }
}
