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
interface SiteInterface
{
    public function __toString(): string;

    /**
     * @return int|string|null
     */
    public function getId();

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getTitle(): ?string;

    public function setTitle(?string $title): void;

    public function getHost(): ?string;

    public function setHost(?string $host): void;

    public function getLocale(): ?string;

    public function setLocale(?string $locale): void;

    public function getEnabledFrom(): ?\DateTimeInterface;

    public function setEnabledFrom(?\DateTimeInterface $enabledFrom = null): void;

    public function getEnabledTo(): ?\DateTimeInterface;

    public function setEnabledTo(?\DateTimeInterface $enabledTo = null): void;

    public function getIsDefault(): bool;

    public function setIsDefault(bool $default): void;

    public function getRelativePath(): ?string;

    public function setRelativePath(?string $path): void;

    public function getEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void;

    public function getMetaDescription(): ?string;

    public function setMetaDescription(?string $metaDescription): void;

    public function getMetaKeywords(): ?string;

    public function setMetaKeywords(?string $metaKeywords): void;

    public function getUrl(): ?string;

    public function isEnabled(): bool;

    public function isLocalhost(): bool;
}
