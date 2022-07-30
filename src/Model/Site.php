<?php

declare(strict_types=1);

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
     * @var mixed
     */
    protected $id;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var \DateTimeInterface|null
     */
    protected $createdAt;

    /**
     * @var \DateTimeInterface|null
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
     * @var string|null
     */
    protected $relativePath;

    /**
     * @var \DateTimeInterface|null
     */
    protected $enabledFrom;

    /**
     * @var \DateTimeInterface|null
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
     * @var string|null
     */
    protected $locale;

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $metaKeywords;

    /**
     * @var string|null
     */
    protected $metaDescription;

    public function __construct()
    {
        $this->enabled = false;
    }

    public function __toString()
    {
        return $this->getName() ?: 'n/a';
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function isEnabled()
    {
        $now = new \DateTime();

        if ($this->getEnabledFrom() instanceof \DateTimeInterface && $this->getEnabledFrom()->format('U') > $now->format('U')) {
            return false;
        }

        if ($this->getEnabledTo() instanceof \DateTimeInterface && $now->format('U') > $this->getEnabledTo()->format('U')) {
            return false;
        }

        return $this->enabled;
    }

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
        return 'localhost' === $this->getHost();
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setHost($host): void
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setFormats($formats): void
    {
        $this->formats = $formats;
    }

    public function getFormats()
    {
        return $this->formats;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRelativePath($path): void
    {
        $this->relativePath = $path;
    }

    public function getRelativePath()
    {
        return $this->relativePath;
    }

    public function setIsDefault($default): void
    {
        $this->isDefault = $default;
    }

    public function getIsDefault()
    {
        return $this->isDefault;
    }

    public function setEnabledFrom(?\DateTimeInterface $enabledFrom = null): void
    {
        $this->enabledFrom = $enabledFrom;
    }

    public function getEnabledFrom()
    {
        return $this->enabledFrom;
    }

    public function setEnabledTo(?\DateTimeInterface $enabledTo = null): void
    {
        $this->enabledTo = $enabledTo;
    }

    public function getEnabledTo()
    {
        return $this->enabledTo;
    }

    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setMetaDescription($metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    public function setMetaKeywords($metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
