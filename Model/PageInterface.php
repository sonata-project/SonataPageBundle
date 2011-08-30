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

interface PageInterface
{
    const PAGE_ROUTE_CMS_NAME = 'page_slug';

    /**
     * Set routeName
     *
     * @param string $routeName
     */
    function setRouteName($routeName);

    function getId();

    function setId($id);

    /**
     * Get routeName
     *
     * @return string $routeName
     */
    function getRouteName();

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
     * @return datetime $createdAt
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
     * @param Application\Sonata\PageBundle\Entity\Page $children
     */
    function addChildren(PageInterface $children);

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection $children
     */
    function getChildren();

    /**
     * Add blocs
     *
     * @param Application\Sonata\PageBundle\Entity\Block $blocs
     */
    function addBlocks(BlockInterface $blocs);

    /**
     * Get blocs
     *
     * @return Doctrine\Common\Collections\Collection $blocs
     */
    function getBlocks();

    /**
     * Set target
     *
     * @param Application\Sonata\PageBundle\Entity\Page $target
     */
    function setTarget(PageInterface $target);

    /**
     * Get target
     *
     * @return Application\Sonata\PageBundle\Entity\Page $target
     */
    function getTarget();

    /**
     * Set parent
     *
     * @param Application\Sonata\PageBundle\Entity\Page $parent
     */
    function setParent(PageInterface $parent);

    /**
     * Get parent
     *
     * @param integer $level default -1
     * @return Application\Sonata\PageBundle\Entity\Page $parent
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
     * @return string $templateCode
     */
    function getTemplateCode();

    function setDecorate($decorate);

    function getDecorate();

    function isHybrid();

    function setPosition($position);

    function getPosition();

    function setRequestMethod($method);

    function getRequestMethod();
}