<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Entity;

class BasePage
{
    const PAGE_ROUTE_CMS_NAME = 'page_slug';

    protected $createdAt;

    protected $updatedAt;

    protected $routeName;

    protected $name;

    protected $slug;

    protected $metaKeyword;

    protected $metaDescription;

    protected $javascript;

    protected $stylesheet;

    protected $enabled;

    protected $publicationDateStart;

    protected $publicationDateEnd;

    protected $loginRequired;
    
    protected $blocks;

    protected $parent;

    protected $children;
    
    protected $template;

    protected $customUrl;

    protected $position = 1;
  
    protected $decorate = true;
    
    /**
     * Set routeName
     *
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
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
     * Set loginRequired
     *
     * @param boolean $loginRequired
     */
    public function setLoginRequired($loginRequired)
    {
        $this->loginRequired = $loginRequired;
    }

    /**
     * Get loginRequired
     *
     * @return boolean $loginRequired
     */
    public function getLoginRequired()
    {
        return $this->loginRequired;
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
        $this->slug = $slug;
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
     * Set publicationDateStart
     *
     * @param datetime $publicationDateStart
     */
    public function setPublicationDateStart($publicationDateStart)
    {
        $this->publicationDateStart = $publicationDateStart;
    }

    /**
     * Get publicationDateStart
     *
     * @return datetime $publicationDateStart
     */
    public function getPublicationDateStart()
    {
        return $this->publicationDateStart;
    }

    /**
     * Set publicationDateEnd
     *
     * @param datetime $publicationDateEnd
     */
    public function setPublicationDateEnd($publicationDateEnd)
    {
        $this->publicationDateEnd = $publicationDateEnd;
    }

    /**
     * Get publicationDateEnd
     *
     * @return datetime $publicationDateEnd
     */
    public function getPublicationDateEnd()
    {
        return $this->publicationDateEnd;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Add children
     *
     * @param Application\Sonata\PageBundle\Entity\Page $children
     */
    public function addChildren(\Sonata\PageBundle\Entity\BasePage $children)
    {
        $this->children[] = $children;
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
     * Add blocs
     *
     * @param Application\Sonata\PageBundle\Entity\Block $blocs
     */
    public function addBlocks(\Sonata\PageBundle\Entity\BaseBlock $blocs)
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
     * @param Application\Sonata\PageBundle\Entity\Page $parent
     */
    public function setParent(\Application\Sonata\PageBundle\Entity\Page $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Application\Sonata\PageBundle\Entity\Page $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set template
     *
     * @param Application\Sonata\PageBundle\Entity\Template $template
     */
    public function setTemplate(\Application\Sonata\PageBundle\Entity\Template $template)
    {
        $this->template = $template;
    }

    /**
     * Get template
     *
     * @return Application\Sonata\PageBundle\Entity\Template $template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function disableBlockLazyLoading()
    {
        if(is_object($this->blocks))
        {
            $this->blocks->setInitialized(true);
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

    public function isHybrid()
    {

        return $this->getRouteName() != self::PAGE_ROUTE_CMS_NAME;
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
}