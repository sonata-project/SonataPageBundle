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
 *
 * @phpstan-import-type PageContent from TransformerInterface
 */
interface SnapshotInterface
{
    /**
     * @return int|string|null
     */
    public function getId();

    public function getRouteName(): ?string;

    public function setRouteName(?string $routeName): void;

    public function getPageAlias(): ?string;

    /**
     * The route alias defines an internal url code that user can use to point
     * to an url. This feature must used with care to avoid to many generated queries.
     */
    public function setPageAlias(?string $pageAlias): void;

    public function getType(): ?string;

    public function setType(?string $type): void;

    public function getEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getUrl(): ?string;

    public function setUrl(?string $url): void;

    public function getPublicationDateStart(): ?\DateTimeInterface;

    public function setPublicationDateStart(?\DateTimeInterface $publicationDateStart = null): void;

    public function getPublicationDateEnd(): ?\DateTimeInterface;

    public function setPublicationDateEnd(?\DateTimeInterface $publicationDateEnd = null): void;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void;

    public function getDecorate(): bool;

    public function setDecorate(bool $decorate): void;

    public function getPosition(): ?int;

    public function setPosition(?int $position): void;

    public function getPage(): ?PageInterface;

    public function setPage(?PageInterface $page = null): void;

    public function getSite(): ?SiteInterface;

    public function setSite(?SiteInterface $site = null): void;

    /**
     * @return array<string, mixed>|null
     *
     * @phpstan-return PageContent|null
     */
    public function getContent(): ?array;

    /**
     * @param array<string, mixed>|null $content
     *
     * @phpstan-param PageContent|null $content
     */
    public function setContent(?array $content): void;

    /**
     * @return int|string|null
     */
    public function getParentId();

    /**
     * @param int|string|null $parentId
     */
    public function setParentId($parentId): void;

    public function isHybrid(): bool;
}
