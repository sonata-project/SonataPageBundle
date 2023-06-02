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
abstract class Site implements SiteInterface, \Stringable
{
    /**
     * @var int|string|null
     */
    protected $id;

    protected ?string $name = null;

    protected ?string $title = null;

    protected ?string $host = null;

    protected ?string $locale = null;

    protected ?\DateTimeInterface $enabledFrom = null;

    protected ?\DateTimeInterface $enabledTo = null;

    protected bool $isDefault = false;

    protected ?string $relativePath = null;

    protected bool $enabled = false;

    protected ?\DateTimeInterface $createdAt = null;

    protected ?\DateTimeInterface $updatedAt = null;

    protected ?string $metaDescription = null;

    protected ?string $metaKeywords = null;

    public function __toString(): string
    {
        return $this->getName() ?? 'n/a';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(?string $host): void
    {
        $this->host = $host;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getEnabledFrom(): ?\DateTimeInterface
    {
        return $this->enabledFrom;
    }

    public function setEnabledFrom(?\DateTimeInterface $enabledFrom = null): void
    {
        $this->enabledFrom = $enabledFrom;
    }

    public function getEnabledTo(): ?\DateTimeInterface
    {
        return $this->enabledTo;
    }

    public function setEnabledTo(?\DateTimeInterface $enabledTo = null): void
    {
        $this->enabledTo = $enabledTo;
    }

    public function getIsDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $default): void
    {
        $this->isDefault = $default;
    }

    public function getRelativePath(): ?string
    {
        return $this->relativePath;
    }

    public function setRelativePath(?string $path): void
    {
        $this->relativePath = $path;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    public function getUrl(): ?string
    {
        if ($this->isLocalhost()) {
            return $this->getRelativePath();
        }

        return sprintf('//%s%s', $this->getHost() ?? '', $this->getRelativePath() ?? '');
    }

    public function isEnabled(): bool
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

    public function isLocalhost(): bool
    {
        return 'localhost' === $this->getHost();
    }
}
