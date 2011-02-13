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

class BaseBlock
{

    protected $settings;

    protected $enabled;

    protected $position;
    
    protected $parent;

    protected $children;
    
    protected $page;
    
    protected $createdAt;

    protected $updatedAt;

    protected $type;

    public function __construct()
    {
        $this->settings = array();
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set settings
     *
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get settings
     *
     * @return array $settings
     */
    public function getSettings()
    {
        return $this->settings;
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
     * Set position
     *
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return integer $position
     */
    public function getPosition()
    {
        return $this->position;
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
     * @param Application\Sonata\PageBundle\Entity\Block $children
     */
    public function addChildren(\Application\Sonata\PageBundle\Entity\Block $children)
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
     * Set parent
     *
     * @param Application\Sonata\PageBundle\Entity\Block $parent
     */
    public function setParent(\Application\Sonata\PageBundle\Entity\Block $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Application\Sonata\PageBundle\Entity\Block $parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set page
     *
     * @param Application\Sonata\PageBundle\Entity\Page $page
     */
    public function setPage(\Application\Sonata\PageBundle\Entity\Page $page)
    {
        $this->page = $page;
    }

    /**
     * Get page
     *
     * @return Application\Sonata\PageBundle\Entity\Page $page
     */
    public function getPage()
    {
        return $this->page;
    }

    public function disableChildrenLazyLoading()
    {
        if(is_object($this->children))
        {
            $this->children->setInitialized(true);
        }
    }

    public function getSetting($name, $default = null)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }

    public function hasParent()
    {
        return $this->getParent() == null;
    }

    public function __toString()
    {
        return 'block (id:'.$this->getId().')';    
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
}