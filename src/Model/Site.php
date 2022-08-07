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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Site implements SiteInterface
{
    /**
     * @var int|string|null
     */
    protected $id = null;

    /**
     * @var string|null
     */
    protected $name = null;

    /**
     * @var string|null
     */
    protected $title = null;

    /**
     * @var string|null
     */
    protected $host = null;

    /**
     * @var string|null
     */
    protected $locale;

    /**
     * @var \DateTimeInterface|null
     */
    protected $enabledFrom = null;

    /**
     * @var \DateTimeInterface|null
     */
    protected $enabledTo = null;

    /**
     * @var bool
     */
    protected $isDefault = false;

    /**
     * @var string|null
     */
    protected $relativePath = null;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var \DateTimeInterface|null
     */
    protected $createdAt = null;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt = null;

    /**
     * @var string|null
     */
    protected $metaDescription = null;

    /**
     * @var string|null
     */
    protected $metaKeywords = null;

    public function __toString()
    {
        return $this->getName() ?? 'n/a';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host): void
    {
        $this->host = $host;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function setLocale($locale): void
    {
        $this->locale = $locale;
    }

    public function getEnabledFrom()
    {
        return $this->enabledFrom;
    }

    public function setEnabledFrom(?\DateTimeInterface $enabledFrom = null): void
    {
        $this->enabledFrom = $enabledFrom;
    }

    public function getEnabledTo()
    {
        return $this->enabledTo;
    }

    public function setEnabledTo(?\DateTimeInterface $enabledTo = null): void
    {
        $this->enabledTo = $enabledTo;
    }

    public function getIsDefault()
    {
        return $this->isDefault;
    }

    public function setIsDefault($default): void
    {
        $this->isDefault = $default;
    }

    public function getRelativePath()
    {
        return $this->relativePath;
    }

    public function setRelativePath($path): void
    {
        $this->relativePath = $path;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    public function setMetaDescription($metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords($metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    public function getUrl()
    {
        if ($this->isLocalhost()) {
            return $this->getRelativePath();
        }

        return sprintf('//%s%s', $this->getHost() ?? '', $this->getRelativePath() ?? '');
    }

    public function isEnabled()
    {
        $now = new \DateTime();

        $enabledFrom = $this->getEnabledFrom();
        if (null !== $enabledFrom && $enabledFrom->format('U') > $now->format('U')) {
            return false;
        }

        $enabledTo = $this->getEnabledTo();
        if (null !== $enabledTo && $now->format('U') > $enabledTo->format('U')) {
            return false;
        }

        return $this->enabled;
    }

    public function isLocalhost()
    {
        return 'localhost' === $this->getHost();
    }
}
