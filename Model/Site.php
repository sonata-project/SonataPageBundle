<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Model;

/**
 * Site.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Site implements SiteInterface
{
    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $relativePath;

    /**
     * @var \DateTime
     */
    protected $enabledFrom;

    /**
     * @var \DateTime
     */
    protected $enabledTo;

    /**
     * @var bool
     */
    protected $isDefault;

    /**
     * @var array
     */
    protected $formats = [];

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $metaKeywords;

    /**
     * @var string
     */
    protected $metaDescription;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->enabled = false;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName() ?: 'n/a';
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        $now = new \DateTime();

        if ($this->getEnabledFrom() instanceof \DateTime && $this->getEnabledFrom()->format('U') > $now->format('U')) {
            return false;
        }

        if ($this->getEnabledTo() instanceof \DateTime && $now->format('U') > $this->getEnabledTo()->format('U')) {
            return false;
        }

        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        if ($this->isLocalhost()) {
            return $this->getRelativePath();
        }

        return sprintf('//%s%s', $this->getHost(), $this->getRelativePath());
    }

    /**
     * @return bool
     */
    public function isLocalhost()
    {
        return 'localhost' == $this->getHost();
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormats($formats)
    {
        $this->formats = $formats;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setRelativePath($relativePath)
    {
        $this->relativePath = $relativePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setIsDefault($default)
    {
        $this->isDefault = $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabledFrom(\DateTime $enabledFrom = null)
    {
        $this->enabledFrom = $enabledFrom;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabledFrom()
    {
        return $this->enabledFrom;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabledTo(\DateTime $enabledTo = null)
    {
        $this->enabledTo = $enabledTo;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabledTo()
    {
        return $this->enabledTo;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }
}
