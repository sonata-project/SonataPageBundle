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

use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\TemplateInterface;

abstract class BasePage implements PageInterface
{

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

    protected $ttl;

    /**
     * Set routeName
     *
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }

    public function setId($id)
    {
        $this->id = $id;
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
     * @param \DateTime $publicationDateStart
     */
    public function setPublicationDateStart(\DateTime $publicationDateStart = null)
    {
        $this->publicationDateStart = $publicationDateStart;
    }

    /**
     * Get publicationDateStart
     *
     * @return \DateTime $publicationDateStart
     */
    public function getPublicationDateStart()
    {
        return $this->publicationDateStart;
    }

    /**
     * Set publicationDateEnd
     *
     * @param \DateTime $publicationDateEnd
     */
    public function setPublicationDateEnd(\DateTime $publicationDateEnd = null)
    {
        $this->publicationDateEnd = $publicationDateEnd;
    }

    /**
     * Get publicationDateEnd
     *
     * @return \DateTime $publicationDateEnd
     */
    public function getPublicationDateEnd()
    {
        return $this->publicationDateEnd;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime $updatedAt
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
    public function addChildren(PageInterface $children)
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
    public function addBlocks(BlockInterface $blocs)
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
    public function setParent(PageInterface $parent)
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
    public function setTemplate(TemplateInterface $template)
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
        if (is_object($this->blocks)) {
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

    public function prePersist()
    {
        $this->createdAt = new \DateTime;
        $this->updatedAt = new \DateTime;
    }

    public function preUpdate()
    {
        $this->updatedAt = new \DateTime;
    }

    public function getTtl()
    {
        if ($this->ttl === null) {
            $ttl = 84600 * 10; // todo : change this value

            foreach($this->getBlocks() as $block) {
                $blockTtl = $block->getTtl();

                $ttl = ($blockTtl < $ttl) ? $blockTtl : $ttl ;
            }

            $this->ttl = $ttl;
        }

        return $this->ttl;
    }
}