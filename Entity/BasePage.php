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

    protected $created_at;

    protected $updated_at;

    protected $route_name;

    protected $name;

    protected $slug;

    protected $meta_keyword;

    protected $meta_description;

    protected $javascript;

    protected $stylesheet;

    protected $enabled;

    protected $publication_date_start;

    protected $publication_date_end;

    protected $login_required;
    
    protected $blocks;

    protected $parent;

    protected $children;
    
    protected $template;

    protected $custom_url;

    protected $position = 1;
  
    protected $decorate = true;
    
    /**
     * Set route_name
     *
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->route_name = $routeName;
    }

    /**
     * Get route_name
     *
     * @return string $routeName
     */
    public function getRouteName()
    {
        return $this->route_name;
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
     * Set login_required
     *
     * @param boolean $loginRequired
     */
    public function setLoginRequired($loginRequired)
    {
        $this->login_required = $loginRequired;
    }

    /**
     * Get login_required
     *
     * @return boolean $loginRequired
     */
    public function getLoginRequired()
    {
        return $this->login_required;
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
     * Set custom_url
     *
     * @param integer $customUrl
     */
    public function setCustomUrl($customUrl)
    {
        $this->custom_url = $customUrl;
    }

    /**
     * Get custom_url
     *
     * @return integer $customUrl
     */
    public function getCustomUrl()
    {
        return $this->custom_url;
    }

    /**
     * Set meta_keyword
     *
     * @param string $metaKeyword
     */
    public function setMetaKeyword($metaKeyword)
    {
        $this->meta_keyword = $metaKeyword;
    }

    /**
     * Get meta_keyword
     *
     * @return string $metaKeyword
     */
    public function getMetaKeyword()
    {
        return $this->meta_keyword;
    }

    /**
     * Set meta_description
     *
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->meta_description = $metaDescription;
    }

    /**
     * Get meta_description
     *
     * @return string $metaDescription
     */
    public function getMetaDescription()
    {
        return $this->meta_description;
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
     * Set publication_date_start
     *
     * @param datetime $publicationDateStart
     */
    public function setPublicationDateStart($publicationDateStart)
    {
        $this->publication_date_start = $publicationDateStart;
    }

    /**
     * Get publication_date_start
     *
     * @return datetime $publicationDateStart
     */
    public function getPublicationDateStart()
    {
        return $this->publication_date_start;
    }

    /**
     * Set publication_date_end
     *
     * @param datetime $publicationDateEnd
     */
    public function setPublicationDateEnd($publicationDateEnd)
    {
        $this->publication_date_end = $publicationDateEnd;
    }

    /**
     * Get publication_date_end
     *
     * @return datetime $publicationDateEnd
     */
    public function getPublicationDateEnd()
    {
        return $this->publication_date_end;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    }

    /**
     * Get updated_at
     *
     * @return datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Add children
     *
     * @param Application\Sonata\PageBundle\Entity\Page $children
     */
    public function addChildren(\Application\Sonata\PageBundle\Entity\Page $children)
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
    public function addBlocks(\Application\Sonata\PageBundle\Entity\Block $blocs)
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