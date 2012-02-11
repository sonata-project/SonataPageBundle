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
use Sonata\PageBundle\Model\PageInterface;

abstract class Site implements SiteInterface
{
    protected $enabled;

    protected $createdAt;

    protected $updatedAt;

    protected $name;

    protected $host;

    protected $relativePath;

    protected $enabledFrom;

    protected $enabledTo;

    protected $isDefault;

    protected $formats = array();

    public function setId($id)
    {
        $this->id = $id;
    }

    public function __construct()
    {
        $this->enabled  = false;
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

    public function getUrl()
    {
        if ($this->isLocalhost()) {
            return $this->getRelativePath();
        }

        return sprintf('http://%s%s', $this->getHost(), $this->getRelativePath());
    }

    /**
     * @return bool
     */
    public function isLocalhost()
    {
        return $this->getHost() == 'localhost';
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
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function __toString()
    {
        return $this->getName()?:'n/a';
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setFormats($formats)
    {
        $this->formats = $formats;
    }

    public function getFormats()
    {
        return $this->formats;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRelativePath($relativePath)
    {
        $this->relativePath = $relativePath;
    }

    public function getRelativePath()
    {
        return $this->relativePath;
    }

    public function setIsDefault($default)
    {
        $this->isDefault = $default;
    }

    public function getIsDefault()
    {
        return $this->isDefault;
    }

    public function setEnabledFrom(\DateTime $enabledFrom)
    {
        $this->enabledFrom = $enabledFrom;
    }

    public function getEnabledFrom()
    {
        return $this->enabledFrom;
    }

    public function setEnabledTo(\DateTime $enabledTo)
    {
        $this->enabledTo = $enabledTo;
    }

    public function getEnabledTo()
    {
        return $this->enabledTo;
    }
}