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
abstract class Snapshot implements SnapshotInterface
{
    /**
     * @var int|string|null
     */
    protected $id = null;

    protected ?string $routeName = null;

    protected ?string $pageAlias = null;

    protected ?string $type = null;

    protected bool $enabled = false;

    protected ?string $name = null;

    protected ?string $url = null;

    protected ?\DateTimeInterface $publicationDateStart = null;

    protected ?\DateTimeInterface $publicationDateEnd = null;

    protected ?\DateTimeInterface $createdAt = null;

    protected ?\DateTimeInterface $updatedAt = null;

    protected bool $decorate = true;

    protected ?int $position = 1;

    protected ?PageInterface $page = null;

    protected ?SiteInterface $site = null;

    /**
     * @var array<string, mixed>|null
     *
     * @phpstan-var PageContent|null
     */
    protected ?array $content = null;

    /**
     * @var array<PageInterface>
     */
    protected array $children = [];

    /**
     * @var int|string|null
     */
    protected $parentId = null;

    public function __toString(): string
    {
        return $this->getName() ?? '-';
    }

    public function getId()
    {
        return $this->id;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getPublicationDateStart(): ?\DateTimeInterface
    {
        return $this->publicationDateStart;
    }

    public function setPublicationDateStart(?\DateTimeInterface $publicationDateStart = null): void
    {
        $this->publicationDateStart = $publicationDateStart;
    }

    public function getPublicationDateEnd(): ?\DateTimeInterface
    {
        return $this->publicationDateEnd;
    }

    public function setPublicationDateEnd(?\DateTimeInterface $publicationDateEnd = null): void
    {
        $this->publicationDateEnd = $publicationDateEnd;
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

    public function getPage(): ?PageInterface
    {
        return $this->page;
    }

    public function setPage(?PageInterface $page = null): void
    {
        $this->page = $page;
    }

    public function getSite(): ?SiteInterface
    {
        return $this->site;
    }

    public function setSite(?SiteInterface $site = null): void
    {
        $this->site = $site;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(?array $content): void
    {
        $this->content = $content;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function setParentId($parentId): void
    {
        $this->parentId = $parentId;
    }

    public function isHybrid(): bool
    {
        return PageInterface::PAGE_ROUTE_CMS_NAME !== $this->getRouteName();
    }
}
