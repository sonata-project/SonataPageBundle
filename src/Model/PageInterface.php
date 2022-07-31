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

use Doctrine\Common\Collections\Collection;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface PageInterface extends \Stringable
{
    public const PAGE_ROUTE_CMS_NAME = 'page_slug';

    /**
     * @return string
     */
    public function __toString();

    /**
     * @return int|string|null
     */
    public function getId();

    /**
     * @param int|string|null $id
     *
     * @return void
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @param string|null $title
     *
     * @return void
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getRouteName();

    /**
     * @param string $routeName
     *
     * @return void
     */
    public function setRouteName($routeName);

    /**
     * @return string|null
     */
    public function getPageAlias();

    /**
     * The route alias defines an internal url code that user can use to point
     * to an url. This feature must used with care to avoid to many generated queries.
     *
     * For performance, all pageAlias must be prefixed by _page_alias_ this will avoid
     * database lookup to load non existent alias
     *
     * @param string|null $pageAlias
     *
     * @return void
     */
    public function setPageAlias($pageAlias);

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @param string|null $type
     *
     * @return void
     */
    public function setType($type);

    /**
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * @param bool $enabled
     *
     * @return void
     */
    public function setEnabled($enabled);

    /**
     * @return string|null $name
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return void
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getSlug();

    /**
     * @param string|null $slug
     *
     * @return void
     */
    public function setSlug($slug);

    /**
     * @return string|null
     */
    public function getUrl();

    /**
     * @param string|null $url
     *
     * @return void
     */
    public function setUrl($url);

    /**
     * @return string|null
     */
    public function getCustomUrl();

    /**
     * @param string|null $customUrl
     *
     * @return void
     */
    public function setCustomUrl($customUrl);

    /**
     * @return string|null
     */
    public function getMetaKeyword();

    /**
     * @param string|null $metaKeyword
     *
     * @return void
     */
    public function setMetaKeyword($metaKeyword);

    /**
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * @param string|null $metaDescription
     *
     * @return void
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string|null
     */
    public function getJavascript();

    /**
     * @param string|null $javascript
     *
     * @return void
     */
    public function setJavascript($javascript);

    /**
     * @return string|null
     */
    public function getStylesheet();

    /**
     * @param string|null $stylesheet
     *
     * @return void
     */
    public function setStylesheet($stylesheet);

    /**
     * @return \DateTimeInterface|null
     */
    public function getCreatedAt();

    /**
     * @return void
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt = null);

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt();

    /**
     * @return void
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null);

    /**
     * @return Collection<array-key, PageInterface>
     */
    public function getChildren();

    /**
     * @param Collection<array-key, PageInterface> $children
     */
    public function setChildren($children): void;

    /**
     * @return void
     */
    public function addChild(self $child);

    /**
     * @return Collection<array-key, PageBlockInterface>
     */
    public function getBlocks();

    /**
     * @return void
     */
    public function addBlock(PageBlockInterface $block);

    /**
     * @param string $code
     *
     * @return PageBlockInterface
     */
    public function getContainerByCode($code);

    /**
     * @param string $type
     *
     * @return array<PageBlockInterface>
     */
    public function getBlocksByType($type);

    /**
     * @param int $level
     *
     * @return PageInterface|null
     */
    public function getParent($level = -1);

    /**
     * @return void
     */
    public function setParent(?self $parent = null);

    /**
     * @return array<PageInterface>
     */
    public function getParents();

    /**
     * @param array<PageInterface> $parents
     *
     * @return void
     */
    public function setParents(array $parents);

    /**
     * @return string|null
     */
    public function getTemplateCode();

    /**
     * @param string|null $templateCode
     *
     * @return void
     */
    public function setTemplateCode($templateCode);

    /**
     * @return bool
     */
    public function getDecorate();

    /**
     * Indicates if the page should be decorated with the CMS outer layout.
     *
     * @param bool $decorate
     *
     * @return void
     */
    public function setDecorate($decorate);

    /**
     * @return int|null
     */
    public function getPosition();

    /**
     * @param int|null $position
     *
     * @return void
     */
    public function setPosition($position);

    /**
     * @return string
     */
    public function getRequestMethod();

    /**
     * @param string $method
     *
     * @return void
     */
    public function setRequestMethod($method);

    /**
     * @param string $method
     *
     * @return bool
     */
    public function hasRequestMethod($method);

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;

    /**
     * @param array<string, mixed> $headers
     *
     * @return void
     */
    public function setHeaders(array $headers = []);

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function addHeader($name, $value);

    /**
     * @return string|null
     */
    public function getRawHeaders();

    /**
     * @param string|null $rawHeaders
     *
     * @return void
     */
    public function setRawHeaders($rawHeaders);

    /**
     * @return SiteInterface|null
     */
    public function getSite();

    /**
     * @return void
     */
    public function setSite(?SiteInterface $site = null);

    /**
     * @return bool
     */
    public function getEdited();

    /**
     * @param bool $edited
     *
     * @return void
     */
    public function setEdited($edited);

    /**
     * @return array<SnapshotInterface>
     */
    public function getSnapshots();

    /**
     * @param array<SnapshotInterface> $snapshots
     */
    public function setSnapshots($snapshots): void;

    /**
     * @return SnapshotInterface|null
     */
    public function getSnapshot();

    public function addSnapshot(SnapshotInterface $snapshot): void;

    /**
     * @return bool
     */
    public function isError();

    /**
     * Returns true if the page is hybrid (symfony action with no parameter).
     *
     * @return bool
     */
    public function isHybrid();

    /**
     * Returns true if the page is dynamic (symfony action with parameter).
     *
     * @return bool
     */
    public function isDynamic();

    /**
     * Returns true if the page is static.
     *
     * @return bool
     */
    public function isCms();

    /**
     * Returns true if the page is internal (no direct access with an url)
     * This is used to define transversal page.
     *
     * @return bool
     */
    public function isInternal();
}
