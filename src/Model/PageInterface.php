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

/**
 * PageInterface.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface PageInterface
{
    public const PAGE_ROUTE_CMS_NAME = 'page_slug';

    /**
     * Returns the id.
     *
     * @return mixed
     */
    public function getId();

    public function setId($id);

    /**
     * @return string $routeName
     */
    public function getRouteName();

    /**
     * @param string $routeName
     */
    public function setRouteName($routeName);

    /**
     * @return string|null $pageAlias
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
     */
    public function setPageAlias($pageAlias);

    /**
     * Returns the page type.
     *
     * @return string|null
     */
    public function getType();

    /**
     * Sets the page type.
     *
     * @param string|null $type
     */
    public function setType($type);

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string $name
     */
    public function getName();

    /**
     * @param string|null $slug
     */
    public function setSlug($slug);

    /**
     * @return string|null
     */
    public function getSlug();

    /**
     * @return string|null
     */
    public function getUrl();

    /**
     * @param string|null $url
     */
    public function setUrl($url);

    /**
     * @param string|null $customUrl
     */
    public function setCustomUrl($customUrl);

    /**
     * @return string|null $customUrl
     */
    public function getCustomUrl();

    /**
     * @param string|null $metaKeyword
     */
    public function setMetaKeyword($metaKeyword);

    /**
     * @return string|null $metaKeyword
     */
    public function getMetaKeyword();

    /**
     * @param string|null $metaDescription
     */
    public function setMetaDescription($metaDescription);

    /**
     * @return string|null $metaDescription
     */
    public function getMetaDescription();

    /**
     * @param string|null $javascript
     */
    public function setJavascript($javascript);

    /**
     * @return string|null $javascript
     */
    public function getJavascript();

    /**
     * @param string|null $stylesheet
     */
    public function setStylesheet($stylesheet);

    /**
     * @return string|null $stylesheet
     */
    public function getStylesheet();

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(?\DateTime $createdAt = null);

    /**
     * @return \DateTime $createdAt
     */
    public function getCreatedAt();

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null);

    /**
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt();

    /**
     * @param PageInterface $children
     */
    public function addChildren(self $children);

    /**
     * @return ArrayCollection|PageInterface[]
     */
    public function getChildren();

    public function addBlocks(PageBlockInterface $block);

    /**
     * @return ArrayCollection|PageBlockInterface[]
     */
    public function getBlocks();

    /**
     * @param pageInterface|null $target
     *
     * NEXT_MAJOR: Remove this method
     *
     * @deprecated since 3.27 and it will be removed on 4.0
     */
    public function setTarget(?self $target = null);

    /**
     * @return pageInterface|null
     *
     * NEXT_MAJOR: Remove this method
     *
     * @deprecated since 3.27 and it will be removed on 4.0
     */
    public function getTarget();

    /**
     * @param PageInterface|null $parent
     */
    public function setParent(?self $parent = null);

    /**
     * @param int $level default -1
     *
     * @return PageInterface|null
     */
    public function getParent($level = -1);

    /**
     * @param string|null $templateCode
     */
    public function setTemplateCode($templateCode);

    /**
     * @return string|null
     */
    public function getTemplateCode();

    /**
     * Indicates if the page should be decorated with the CMS outer layout.
     *
     * @param bool $decorate
     */
    public function setDecorate($decorate);

    /**
     * Returns true if the page can be decorate.
     *
     * @return bool
     */
    public function getDecorate();

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

    /**
     * @param int $position
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param string|null $method
     */
    public function setRequestMethod($method);

    /**
     * @return string|null
     */
    public function getRequestMethod();

    public function setHeaders(array $headers = []);

    /**
     * @param string $name
     */
    public function addHeader($name, $value);

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @param PageInterface[] $parents
     */
    public function setParents(array $parents);

    /**
     * @return PageInterface[]
     */
    public function getParents();

    /**
     * @param string $method
     *
     * @return bool
     */
    public function hasRequestMethod($method);

    public function setSite(?SiteInterface $site = null);

    /**
     * @return SiteInterface|null
     */
    public function getSite();

    /**
     * @param string|null $rawHeaders
     */
    public function setRawHeaders($rawHeaders);

    /**
     * @return bool
     */
    public function getEdited();

    /**
     * @param bool $edited
     */
    public function setEdited($edited);

    /**
     * @return bool
     */
    public function isError();

    /**
     * Return the title.
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set the title.
     *
     * @param string|null $title
     */
    public function setTitle($title);
}
