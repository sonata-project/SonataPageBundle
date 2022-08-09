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

use Doctrine\Common\Collections\Collection;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface PageInterface extends \Stringable
{
    public const PAGE_ROUTE_CMS_NAME = 'page_slug';

    public function __toString(): string;

    /**
     * @return int|string|null
     */
    public function getId();

    /**
     * @param int|string|null $id
     */
    public function setId($id): void;

    public function getTitle(): ?string;

    public function setTitle(?string $title): void;

    public function getRouteName(): ?string;

    public function setRouteName(?string $routeName): void;

    public function getPageAlias(): ?string;

    /**
     * The route alias defines an internal url code that user can use to point
     * to an url. This feature must used with care to avoid to many generated queries.
     *
     * For performance, all pageAlias must be prefixed by _page_alias_ this will avoid
     * database lookup to load non existent alias
     */
    public function setPageAlias(?string $pageAlias): void;

    public function getType(): ?string;

    public function setType(?string $type): void;

    public function getEnabled(): bool;

    public function setEnabled(bool $enabled): void;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getSlug(): ?string;

    public function setSlug(?string $slug): void;

    public function getUrl(): ?string;

    public function setUrl(?string $url): void;

    public function getCustomUrl(): ?string;

    public function setCustomUrl(?string $customUrl): void;

    public function getMetaKeyword(): ?string;

    public function setMetaKeyword(?string $metaKeyword): void;

    public function getMetaDescription(): ?string;

    public function setMetaDescription(?string $metaDescription): void;

    public function getJavascript(): ?string;

    public function setJavascript(?string $javascript): void;

    public function getStylesheet(): ?string;

    public function setStylesheet(?string $stylesheet): void;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void;

    /**
     * @return Collection<array-key, PageInterface>
     */
    public function getChildren(): Collection;

    /**
     * @param Collection<array-key, PageInterface> $children
     */
    public function setChildren(Collection $children): void;

    public function addChild(self $child): void;

    /**
     * @return Collection<array-key, PageBlockInterface>
     */
    public function getBlocks(): Collection;

    public function addBlock(PageBlockInterface $block): void;

    public function getContainerByCode(string $code): ?PageBlockInterface;

    /**
     * @return array<PageBlockInterface>
     */
    public function getBlocksByType(string $type): array;

    public function getParent(int $level = -1): ?self;

    public function setParent(?self $parent = null): void;

    /**
     * @return array<PageInterface>
     */
    public function getParents(): array;

    /**
     * @param array<PageInterface> $parents
     */
    public function setParents(array $parents): void;

    public function getTemplateCode(): ?string;

    public function setTemplateCode(?string $templateCode): void;

    public function getDecorate(): bool;

    /**
     * Indicates if the page should be decorated with the CMS outer layout.
     */
    public function setDecorate(bool $decorate): void;

    public function getPosition(): ?int;

    public function setPosition(?int $position): void;

    public function getRequestMethod(): ?string;

    public function setRequestMethod(?string $method): void;

    public function hasRequestMethod(string $method): bool;

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;

    /**
     * @param array<string, mixed> $headers
     */
    public function setHeaders(array $headers = []): void;

    /**
     * @param mixed $value
     */
    public function addHeader(string $name, $value): void;

    public function getRawHeaders(): ?string;

    public function setRawHeaders(?string $rawHeaders): void;

    public function getSite(): ?SiteInterface;

    public function setSite(?SiteInterface $site = null): void;

    public function getEdited(): bool;

    public function setEdited(bool $edited): void;

    /**
     * @return array<SnapshotInterface>
     */
    public function getSnapshots(): array;

    /**
     * @param array<SnapshotInterface> $snapshots
     */
    public function setSnapshots(array $snapshots): void;

    public function getSnapshot(): ?SnapshotInterface;

    public function addSnapshot(SnapshotInterface $snapshot): void;

    public function isError(): bool;

    /**
     * Returns true if the page is hybrid (symfony action with no parameter).
     */
    public function isHybrid(): bool;

    /**
     * Returns true if the page is dynamic (symfony action with parameter).
     */
    public function isDynamic(): bool;

    /**
     * Returns true if the page is static.
     */
    public function isCms(): bool;

    /**
     * Returns true if the page is internal (no direct access with an url)
     * This is used to define transversal page.
     */
    public function isInternal(): bool;
}
