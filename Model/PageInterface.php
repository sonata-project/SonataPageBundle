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
    function getId();

    /**
     * @param mixed $id
     */
    function setId($id);

    /**
     * Get routeName
     *
     * @return string $routeName
     */
    function getRouteName();

    /**
     * Set routeName
     *
     * @param string $routeName
     */
    function setRouteName($routeName);

    /**
     * Get $outeAlias
     *
     * @return string $pageAlias
     */
    function getPageAlias();

    /**
     * The route alias defines an internal url code that user can use to point
     * to an url. This feature must used with care to avoid to many generated queries
     *
     * For performance, all pageAlias must be prefixed by _page_alias_ this will avoid
     * database lookup to load non existant alias
     *
     * Set pageAlias
     *
     * @param string $pageAlias
     */
    function setPageAlias($pageAlias);

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    function setEnabled($enabled);

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    function getEnabled();

    /**
     * Set name
     *
     * @param string $name
     */
    function setName($name);

    /**
     * Get name
     *
     * @return string $name
     */
    function getName();

    /**
     * Set slug
     *
     * @param string $slug
     */
    function setSlug($slug);

    /**
     * Get slug
     *
     * @return string
     */
    function getSlug();

    /**
     * Get url
     *
     * @return string
     */
    function getUrl();

    /**
     * Set Url
     *
     * @param string $url
     *
     * @return void
     */
    function setUrl($url);

    /**
     * Set customUrl
     *
     * @param string $customUrl
     */
    function setCustomUrl($customUrl);

    /**
     * Get customUrl
     *
     * @return integer $customUrl
     */
    function getCustomUrl();

    /**
     * Set metaKeyword
     *
     * @param string $metaKeyword
     */
    function setMetaKeyword($metaKeyword);

    /**
     * Get metaKeyword
     *
     * @return string $metaKeyword
     */
    function getMetaKeyword();

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     */
    function setMetaDescription($metaDescription);

    /**
     * Get metaDescription
     *
     * @return string $metaDescription
     */
    function getMetaDescription();

    /**
     * Set javascript
     *
     * @param string $javascript
     */
    function setJavascript($javascript);

    /**
     * Get javascript
     *
     * @return string $javascript
     */
    function getJavascript();

    /**
     * Set stylesheet
     *
     * @param string $stylesheet
     */
    function setStylesheet($stylesheet);

    /**
     * Get stylesheet
     *
     * @return string $stylesheet
     */
    function getStylesheet();

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get createdAt
     *
     * @return \DateTime $createdAt
     */
    function getCreatedAt();

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     */
    function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updatedAt
     *
     * @return \DateTime $updatedAt
     */
    function getUpdatedAt();

    /**
     * Add children
     *
     * @param PageInterface $children
     */
    function addChildren(PageInterface $children);

    /**
     * Get children
     *
     * @return array
     */
    function getChildren();

    /**
     * Add blocs
     *
     * @param BlockInterface $bloc
     */
    function addBlocks(BlockInterface $bloc);

    /**
     * Get blocs
     *
     * @return array
     */
    function getBlocks();

    /**
     * @param PageInterface $target
     *
     * @return void
     */
    function setTarget(PageInterface $target = null);

    /**
     * Get target
     *
     * @return PageInterface
     */
    function getTarget();

    /**
     * Set parent
     *
     * @param PageInterface $parent
     */
    function setParent(PageInterface $parent = null);

    /**
     * Get parent
     *
     * @param integer $level default -1
     *
     * @return PageInterface $parent
     */
    function getParent($level = -1);

    /**
     * Set template
     *
     * @param string $templateCode
     */
    function setTemplateCode($templateCode);

    /**
     * Get template
     *
     * @return string
     */
    function getTemplateCode();

    /**
     * Indicates if the page should be decorated with the CMS outter layout
     *
     *
     * @param boolean $decorate
     *
     * @return void
     */
    function setDecorate($decorate);

    /**
     * Returns true if the page can be decorate
     *
     * @return boolean
     */
    function getDecorate();

    /**
     * Returns true if the page is hybrid (symfony action with no parameter)
     *
     * @return boolean
     */
    function isHybrid();

    /**
     * Returns true if the page is dynamic (symfony action with parameter)
     *
     * @return boolean
     */
    function isDynamic();

    /**
     * Returns true if the page is static
     *
     * @return boolean
     */
    function isCms();

    /**
     * Returns true if the page is internal (no direct access with an url)
     * This is used to define transversal page
     *
     * @return boolean
     */
    function isInternal();

    /**
     * @param int $position
     *
     * @return void
     */
    function setPosition($position);

    /**
     * @return int
     */
    function getPosition();

    /**
     * @param string $method
     *
     * @return void
     */
    function setRequestMethod($method);

    /**
     * @return string
     */
    function getRequestMethod();

    /**
     * @param array $headers
     *
     * @return void
     */
    function setHeaders(array $headers = array());

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return void
     */
    function addHeader($name, $value);

    /**
     * @return array
     */
    function getHeaders();

    /**
     * @param array $parents
     */
    function setParents(array $parents);

    /**
     * @return array
     */
    function getParents();

    /**
     * Return the TTL value in second
     *
     *
     * @return integer
     */
    function getTtl();

    /**
     * @param string $method
     *
     * @return bool
     */
    function hasRequestMethod($method);

    /**
     * @param SiteInterface $site
     */
    function setSite(SiteInterface $site);

    /**
     * @return SiteInterface
     */
    function getSite();

    /**
     * @param array $rawHeaders
     */
    function setRawHeaders($rawHeaders);

    /**
     * @return boolean
     */
    function getEdited();

    /**
     * @param boolean $edited
     */
    function setEdited($edited);

    /**
     * @return boolean
     */
    function isError();

    /**
     * Return the title.
     *
     * @return string
     */
    function getTitle();

    /**
     * Set the title.
     *
     * @param string $title
     */
    function setTitle($title);
}