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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Page implements PageInterface
{
    /**
     * @var int|string|null
     */
    protected $id;

    protected ?string $title = null;

    protected ?string $routeName = PageInterface::PAGE_ROUTE_CMS_NAME;

    protected ?string $pageAlias = null;

    protected ?string $type = null;

    protected bool $enabled = false;

    protected ?string $name = null;

    protected ?string $slug = null;

    protected ?string $url = null;

    protected ?string $customUrl = null;

    protected ?string $metaKeyword = null;

    protected ?string $metaDescription = null;

    protected ?string $javascript = null;

    protected ?string $stylesheet = null;

    protected ?\DateTimeInterface $createdAt = null;

    protected ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<array-key, PageInterface>
     */
    protected Collection $children;

    /**
     * @var Collection<array-key, PageBlockInterface>
     */
    protected Collection $blocks;

    protected ?PageInterface $parent = null;

    /**
     * @var array<PageInterface>|null
     */
    protected ?array $parents = null;

    protected ?string $templateCode = null;

    protected bool $decorate = true;

    protected ?int $position = 1;

    protected ?string $requestMethod = 'GET|POST|HEAD|DELETE|PUT';

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $headers = null;

    protected ?string $rawHeaders = null;

    protected ?SiteInterface $site = null;

    protected bool $edited = true;

    /**
     * @var array<SnapshotInterface>
     */
    protected array $snapshots = [];

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? '-';
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(?string $routeName): void
    {
        $this->routeName = $routeName;
    }

    public function getPageAlias(): ?string
    {
        return $this->pageAlias;
    }

    public function setPageAlias(?string $pageAlias): void
    {
        if (!str_starts_with((string) $pageAlias, '_page_alias_')) {
            $pageAlias = '_page_alias_'.$pageAlias;
        }

        $this->pageAlias = $pageAlias;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getCustomUrl(): ?string
    {
        return $this->customUrl;
    }

    public function setCustomUrl(?string $customUrl): void
    {
        $this->customUrl = $customUrl;
    }

    public function getMetaKeyword(): ?string
    {
        return $this->metaKeyword;
    }

    public function setMetaKeyword(?string $metaKeyword): void
    {
        $this->metaKeyword = $metaKeyword;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getJavascript(): ?string
    {
        return $this->javascript;
    }

    public function setJavascript(?string $javascript): void
    {
        $this->javascript = $javascript;
    }

    public function getStylesheet(): ?string
    {
        return $this->stylesheet;
    }

    public function setStylesheet(?string $stylesheet): void
    {
        $this->stylesheet = $stylesheet;
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

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    public function addChild(PageInterface $child): void
    {
        $this->children[] = $child;

        $child->setParent($this);
    }

    public function removeChild(PageInterface $child): void
    {
        $this->children->removeElement($child);
    }

    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function addBlock(PageBlockInterface $block): void
    {
        $block->setPage($this);

        $this->blocks[] = $block;
    }

    public function removeBlock(PageBlockInterface $block): void
    {
        $this->blocks->removeElement($block);
        $block->setPage();
    }

    public function getContainerByCode(string $code): ?PageBlockInterface
    {
        foreach ($this->getBlocks() as $block) {
            if (\in_array($block->getType(), ['sonata.page.block.container', 'sonata.block.service.container'], true) && $block->getSetting('code') === $code) {
                return $block;
            }
        }

        return null;
    }

    public function getBlocksByType(string $type): array
    {
        $blocks = [];

        foreach ($this->getBlocks() as $block) {
            if ($type === $block->getType()) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    public function getParent(int $level = -1): ?PageInterface
    {
        if (-1 === $level) {
            return $this->parent;
        }

        $parents = $this->getParents();

        if ($level < 0) {
            $level = \count($parents) + $level;
        }

        return $parents[$level] ?? null;
    }

    public function setParent(?PageInterface $parent = null): void
    {
        $this->parent = $parent;
    }

    public function getParents(): array
    {
        if (null === $this->parents) {
            $parent = $this->getParent();
            $parents = [];

            while (null !== $parent) {
                $parents[] = $parent;

                $parent = $parent->getParent();
            }

            $this->parents = array_reverse($parents);
        }

        return $this->parents;
    }

    public function setParents(array $parents): void
    {
        $this->parents = $parents;
    }

    public function getTemplateCode(): ?string
    {
        return $this->templateCode;
    }

    public function setTemplateCode(?string $templateCode): void
    {
        $this->templateCode = $templateCode;
    }

    public function getDecorate(): bool
    {
        return $this->decorate;
    }

    public function setDecorate(bool $decorate): void
    {
        $this->decorate = $decorate;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function setRequestMethod(?string $method): void
    {
        $this->requestMethod = $method;
    }

    public function hasRequestMethod(string $method): bool
    {
        $method = strtoupper($method);

        if (!\in_array($method, ['PUT', 'POST', 'GET', 'DELETE', 'HEAD'], true)) {
            return false;
        }

        $requestMethod = $this->getRequestMethod();

        return null === $requestMethod || str_contains($requestMethod, $method);
    }

    public function getHeaders(): array
    {
        if (null === $this->headers) {
            $rawHeaders = $this->getRawHeaders();

            $this->headers = null !== $rawHeaders ? $this->getHeadersAsArray($rawHeaders) : [];
        }

        return $this->headers;
    }

    public function setHeaders(array $headers = []): void
    {
        $this->headers = [];
        $this->rawHeaders = null;

        foreach ($headers as $name => $header) {
            $this->addHeader($name, $header);
        }
    }

    public function addHeader(string $name, mixed $value): void
    {
        $headers = $this->getHeaders();

        $headers[$name] = $value;

        $this->headers = $headers;

        $this->rawHeaders = $this->getHeadersAsString($headers);
    }

    public function getRawHeaders(): ?string
    {
        return $this->rawHeaders;
    }

    public function setRawHeaders(?string $rawHeaders): void
    {
        $this->setHeaders(null !== $rawHeaders ? $this->getHeadersAsArray($rawHeaders) : []);
    }

    public function getSite(): ?SiteInterface
    {
        return $this->site;
    }

    public function setSite(?SiteInterface $site = null): void
    {
        $this->site = $site;
    }

    public function getEdited(): bool
    {
        return $this->edited;
    }

    public function setEdited(bool $edited): void
    {
        $this->edited = $edited;
    }

    public function getSnapshots(): array
    {
        return $this->snapshots;
    }

    public function setSnapshots(array $snapshots): void
    {
        $this->snapshots = $snapshots;
    }

    public function getSnapshot(): ?SnapshotInterface
    {
        return $this->snapshots[0] ?? null;
    }

    public function addSnapshot(SnapshotInterface $snapshot): void
    {
        $this->snapshots[] = $snapshot;

        $snapshot->setPage($this);
    }

    public function isError(): bool
    {
        return str_starts_with($this->getRouteName() ?? '', '_page_internal_error_');
    }

    public function isHybrid(): bool
    {
        return PageInterface::PAGE_ROUTE_CMS_NAME !== $this->getRouteName() && !$this->isInternal();
    }

    public function isDynamic(): bool
    {
        return $this->isHybrid() && str_contains($this->getUrl() ?? '', '{');
    }

    public function isCms(): bool
    {
        return PageInterface::PAGE_ROUTE_CMS_NAME === $this->getRouteName() && !$this->isInternal();
    }

    public function isInternal(): bool
    {
        return str_starts_with($this->getRouteName() ?? '', '_page_internal_');
    }

    /**
     * @return array<string, string>
     *
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    private function getHeadersAsArray(string $rawHeaders): array
    {
        $headers = [];

        foreach (explode("\r\n", $rawHeaders) as $header) {
            if (str_contains($header, ':')) {
                [$name, $headerStr] = explode(':', $header, 2);
                $headers[trim($name)] = trim($headerStr);
            }
        }

        return $headers;
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function getHeadersAsString(array $headers): string
    {
        $rawHeaders = [];

        foreach ($headers as $name => $header) {
            $rawHeaders[] = sprintf('%s: %s', trim($name), trim($header));
        }

        $rawHeaders = implode("\r\n", $rawHeaders);

        return $rawHeaders;
    }
}
