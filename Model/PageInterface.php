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

/**
 * PageInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface PageInterface
{
    const PAGE_ROUTE_CMS_NAME = 'page_slug';

    /**
     * Returns the id
     *
     * @return mixed
     */
    public function getId();

    /**
     * @param mixed $id
     */
    public function setId($id);

    /**
     * Get routeName
     *
     * @return string $routeName
     */
    public function getRouteName();

    /**
     * Set routeName
     *
     * @param string $routeName
     */
    public function setRouteName($routeName);

    /**
     * Get pageAlias
     *
     * @return string $pageAlias
     */
    public function getPageAlias();

    /**
     * The route alias defines an internal url code that user can use to point
     * to an url. This feature must used with care to avoid to many generated queries
     *
     * For performance, all pageAlias must be prefixed by _page_alias_ this will avoid
     * database lookup to load non existent alias
     *
     * Set pageAlias
     *
     * @param string $pageAlias
     */
    public function setPageAlias($pageAlias);

    /**
     * Returns the page type
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the page type
     *
     * @param string $type
     */
    public function setType($type);

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled);

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled();

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName();

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug);

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug();

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl();

    /**
     * Set Url
     *
     * @param string $url
     *
     * @return void
     */
    public function setUrl($url);

    /**
     * Set customUrl
     *
     * @param string $customUrl
     */
    public function setCustomUrl($customUrl);

    /**
     * Get customUrl
     *
     * @return integer $customUrl
     */
    public function getCustomUrl();

    /**
     * Set metaKeyword
     *
     * @param string $metaKeyword
     */
    public function setMetaKeyword($metaKeyword);

    /**
     * Get metaKeyword
     *
     * @return string $metaKeyword
     */
    public function getMetaKeyword();

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get metaDescription
     *
     * @return string $metaDescription
     */
    public function getMetaDescription();

    /**
     * Set javascript
     *
     * @param string $javascript
     */
    public function setJavascript($javascript);

    /**
     * Get javascript
     *
     * @return string $javascript
     */
    public function getJavascript();

    /**
     * Set stylesheet
     *
     * @param string $stylesheet
     */
    public function setStylesheet($stylesheet);

    /**
     * Get stylesheet
     *
     * @return string $stylesheet
     */
    public function getStylesheet();

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get createdAt
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt();

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updatedAt
     *
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt();

    /**
     * Add children
     *
     * @param PageInterface $children
     */
    public function addChildren(PageInterface $children);

    /**
     * Get children
     *
     * @return array
     */
    public function getChildren();

    /**
     * Add blocs
     *
     * @param PageBlockInterface $block
     */
    public function addBlocks(PageBlockInterface $block);

    /**
     * Get blocs
     *
     * @return array
     */
    public function getBlocks();

    /**
     * @param PageInterface $target
     *
     * @return void
     */
    public function setTarget(PageInterface $target = null);

    /**
     * Get target
     *
     * @return PageInterface
     */
    public function getTarget();

    /**
     * Set parent
     *
     * @param PageInterface $parent
     */
    public function setParent(PageInterface $parent = null);

    /**
     * Get parent
     *
     * @param integer $level default -1
     *
     * @return PageInterface $parent
     */
    public function getParent($level = -1);

    /**
     * Set template
     *
     * @param string $templateCode
     */
    public function setTemplateCode($templateCode);

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplateCode();

    /**
     * Indicates if the page should be decorated with the CMS outer layout
     *
     *
     * @param boolean $decorate
     *
     * @return void
     */
    public function setDecorate($decorate);

    /**
     * Returns true if the page can be decorate
     *
     * @return boolean
     */
    public function getDecorate();

    /**
     * Returns true if the page is hybrid (symfony action with no parameter)
     *
     * @return boolean
     */
    public function isHybrid();

    /**
     * Returns true if the page is dynamic (symfony action with parameter)
     *
     * @return boolean
     */
    public function isDynamic();

    /**
     * Returns true if the page is static
     *
     * @return boolean
     */
    public function isCms();

    /**
     * Returns true if the page is internal (no direct access with an url)
     * This is used to define transversal page
     *
     * @return boolean
     */
    public function isInternal();

    /**
     * @param int $position
     *
     * @return void
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param string $method
     *
     * @return void
     */
    public function setRequestMethod($method);

    /**
     * @return string
     */
    public function getRequestMethod();

    /**
     * @param array $headers
     *
     * @return void
     */
    public function setHeaders(array $headers = array());

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function addHeader($name, $value);

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @param array $parents
     */
    public function setParents(array $parents);

    /**
     * @return array
     */
    public function getParents();

    /**
     * @param string $method
     *
     * @return bool
     */
    public function hasRequestMethod($method);

    /**
     * @param SiteInterface $site
     */
    public function setSite(SiteInterface $site);

    /**
     * @return SiteInterface
     */
    public function getSite();

    /**
     * @param array $rawHeaders
     */
    public function setRawHeaders($rawHeaders);

    /**
     * @return boolean
     */
    public function getEdited();

    /**
     * @param boolean $edited
     */
    public function setEdited($edited);

    /**
     * @return boolean
     */
    public function isError();

    /**
     * Return the title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set the title.
     *
     * @param string $title
     */
    public function setTitle($title);
}
