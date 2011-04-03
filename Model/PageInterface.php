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
     * Set loginRequired
     *
     * @param boolean $loginRequired
     */
    function setLoginRequired($loginRequired);

    /**
     * Get loginRequired
     *
     * @return boolean $loginRequired
     */
    function getLoginRequired();

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
     * @return string $slug
     */
    function getSlug();

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
     * @param text $javascript
     */
    function setJavascript($javascript);

    /**
     * Get javascript
     *
     * @return text $javascript
     */
    function getJavascript();

    /**
     * Set stylesheet
     *
     * @param text $stylesheet
     */
    function setStylesheet($stylesheet);

    /**
     * Get stylesheet
     *
     * @return text $stylesheet
     */
    function getStylesheet();

    /**
     * Set publicationDateStart
     *
     * @param datetime $publicationDateStart
     */
    function setPublicationDateStart(\DateTime $publicationDateStart = null);

    /**
     * Get publicationDateStart
     *
     * @return datetime $publicationDateStart
     */
    function getPublicationDateStart();

    /**
     * Set publicationDateEnd
     *
     * @param datetime $publicationDateEnd
     */
    function setPublicationDateEnd(\DateTime $publicationDateEnd = null);

    /**
     * Get publicationDateEnd
     *
     * @return datetime $publicationDateEnd
     */
    function getPublicationDateEnd();

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
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
     * @param datetime $updatedAt
     */
    function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updatedAt
     *
     * @return datetime $updatedAt
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
     * Set parent
     *
     * @param Application\Sonata\PageBundle\Entity\Page $parent
     */
    function setParent(PageInterface $parent);

    /**
     * Get parent
     *
     * @return Application\Sonata\PageBundle\Entity\Page $parent
     */
    function getParent();

    /**
     * Set template
     *
     * @param Application\Sonata\PageBundle\Entity\Template $template
     */
    function setTemplate(TemplateInterface $template);

    /**
     * Get template
     *
     * @return Application\Sonata\PageBundle\Entity\Template $template
     */
    function getTemplate();

    function setDecorate($decorate);

    function getDecorate();

    function isHybrid();

    function __toString();

    function setPosition($position);

    function getPosition();

}