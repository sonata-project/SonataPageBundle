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
final class SnapshotPageProxy implements SnapshotPageProxyInterface
{
    private ?PageInterface $page = null;

    /**
     * @var array<PageInterface>|null
     */
    private ?array $parents = null;

    public function __construct(
        private SnapshotManagerInterface $manager,
        private TransformerInterface $transformer,
        private SnapshotInterface $snapshot
    ) {
    }

    public function __toString(): string
    {
        return (string) $this->getPage();
    }

    /**
     * @return array<mixed>
     */
    public function __serialize(): array
    {
        return [
            'pageId' => $this->getPage()->getId(),
            'snapshotId' => $this->snapshot->getId(),
        ];
    }

    /**
     * @param array<mixed> $data
     */
    public function __unserialize(array $data): void
    {
        // TODO: Implement __unserialize() method.
    }

    /**
     * Serialize a snapshot page proxy.
     */
    public function serialize(): string
    {
        return serialize([
            'pageId' => $this->getPage()->getId(),
            'snapshotId' => $this->snapshot->getId(),
        ]);
    }

    /**
     * Unserialize a snapshot page proxy.
     */
    public function unserialize($data): void
    {
        // TODO: Implement unserialize() method.
    }

    public function getId()
    {
        return $this->getPage()->getId();
    }

    public function setId($id): void
    {
        $this->getPage()->setId($id);
    }

    public function getTitle(): ?string
    {
        return $this->getPage()->getTitle();
    }

    public function setTitle(?string $title): void
    {
        $this->getPage()->setTitle($title);
    }

    public function getRouteName(): ?string
    {
        return $this->getPage()->getRouteName();
    }

    public function setRouteName(?string $routeName): void
    {
        $this->getPage()->setRouteName($routeName);
    }

    public function getPageAlias(): ?string
    {
        return $this->getPage()->getPageAlias();
    }

    public function setPageAlias(?string $pageAlias): void
    {
        $this->getPage()->setPageAlias($pageAlias);
    }

    public function getType(): ?string
    {
        return $this->getPage()->getType();
    }

    public function setType(?string $type): void
    {
        $this->getPage()->setType($type);
    }

    public function getEnabled(): bool
    {
        return $this->getPage()->getEnabled();
    }

    public function setEnabled(bool $enabled): void
    {
        $this->getPage()->setEnabled($enabled);
    }

    public function getName(): ?string
    {
        return $this->getPage()->getName();
    }

    public function setName(?string $name): void
    {
        $this->getPage()->setName($name);
    }

    public function getSlug(): ?string
    {
        return $this->getPage()->getSlug();
    }

    public function setSlug(?string $slug): void
    {
        $this->getPage()->setSlug($slug);
    }

    public function getUrl(): ?string
    {
        return $this->getPage()->getUrl();
    }

    public function setUrl(?string $url): void
    {
        $this->getPage()->setUrl($url);
    }

    public function getCustomUrl(): ?string
    {
        return $this->getPage()->getCustomUrl();
    }

    public function setCustomUrl(?string $customUrl): void
    {
        $this->getPage()->setCustomUrl($customUrl);
    }

    public function getMetaKeyword(): ?string
    {
        return $this->getPage()->getMetaKeyword();
    }

    public function setMetaKeyword(?string $metaKeyword): void
    {
        $this->getPage()->setMetaKeyword($metaKeyword);
    }

    public function getMetaDescription(): ?string
    {
        return $this->getPage()->getMetaDescription();
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->getPage()->setMetaDescription($metaDescription);
    }

    public function getJavascript(): ?string
    {
        return $this->getPage()->getJavascript();
    }

    public function setJavascript(?string $javascript): void
    {
        $this->getPage()->setJavascript($javascript);
    }

    public function getStylesheet(): ?string
    {
        return $this->getPage()->getStylesheet();
    }

    public function setStylesheet(?string $stylesheet): void
    {
        $this->getPage()->setStylesheet($stylesheet);
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->getPage()->getCreatedAt();
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void
    {
        $this->getPage()->setCreatedAt($createdAt);
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->getPage()->getUpdatedAt();
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void
    {
        $this->getPage()->setUpdatedAt($updatedAt);
    }

    public function getChildren(): Collection
    {
        if (0 === $this->getPage()->getChildren()->count()) {
            $this->getPage()->setChildren(new SnapshotChildrenCollection($this->transformer, $this->getPage()));
        }

        return $this->getPage()->getChildren();
    }

    public function setChildren(Collection $children): void
    {
        $this->getPage()->setChildren($children);
    }

    public function addChild(PageInterface $child): void
    {
        $this->getPage()->addChild($child);
    }

    public function removeChild(PageInterface $child): void
    {
        // NEXT_MAJOR remove check
        if (method_exists($this->getPage(), 'removeChild')) {
            $this->getPage()->removeChild($child);
        }
    }

    public function getBlocks(): Collection
    {
        if (0 === \count($this->getPage()->getBlocks())) {
            $content = $this->snapshot->getContent();

            if (null !== $content) {
                foreach ($content['blocks'] as $rawBlock) {
                    $block = $this->transformer->loadBlock($rawBlock, $this);
                    $this->addBlock($block);

                    $block->setPage($this);
                }
            }
        }

        return $this->getPage()->getBlocks();
    }

    public function addBlock(PageBlockInterface $block): void
    {
        $this->getPage()->addBlock($block);
    }

    public function removeBlock(PageBlockInterface $block): void
    {
        // NEXT_MAJOR remove check
        if (method_exists($this->getPage(), 'removeBlock')) {
            $this->getPage()->removeBlock($block);
        }
    }

    public function getContainerByCode(string $code): ?PageBlockInterface
    {
        return $this->getPage()->getContainerByCode($code);
    }

    public function getBlocksByType(string $type): array
    {
        return $this->getPage()->getBlocksByType($type);
    }

    public function getParent(int $level = -1): ?PageInterface
    {
        $parents = $this->getParents();

        if ($level < 0) {
            $level = \count($parents) + $level;
        }

        return $parents[$level] ?? null;
    }

    public function setParent(?PageInterface $parent = null): void
    {
        $this->getPage()->setParent($parent);
    }

    public function getParents(): array
    {
        if (null === $this->parents) {
            $parents = [];

            $snapshot = $this->snapshot;

            while (true) {
                $content = $snapshot->getContent();

                if (!isset($content['parent_id'])) {
                    break;
                }

                $snapshot = $this->manager->findEnableSnapshot([
                    'pageId' => $content['parent_id'],
                ]);

                if (null === $snapshot) {
                    break;
                }

                $parents[] = new self($this->manager, $this->transformer, $snapshot);
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
        return $this->getPage()->getTemplateCode();
    }

    public function setTemplateCode(?string $templateCode): void
    {
        $this->getPage()->setTemplateCode($templateCode);
    }

    public function getDecorate(): bool
    {
        return $this->getPage()->getDecorate();
    }

    public function setDecorate(bool $decorate): void
    {
        $this->getPage()->setDecorate($decorate);
    }

    public function getPosition(): ?int
    {
        return $this->getPage()->getPosition();
    }

    public function setPosition(?int $position): void
    {
        $this->getPage()->setPosition($position);
    }

    public function getRequestMethod(): ?string
    {
        return $this->getPage()->getRequestMethod();
    }

    public function setRequestMethod(?string $method): void
    {
        $this->getPage()->setRequestMethod($method);
    }

    public function hasRequestMethod(string $method): bool
    {
        return $this->getPage()->hasRequestMethod($method);
    }

    public function getHeaders(): array
    {
        return $this->getPage()->getHeaders();
    }

    public function setHeaders(array $headers = []): void
    {
        $this->getPage()->setHeaders($headers);
    }

    public function addHeader(string $name, mixed $value): void
    {
        $this->getPage()->addHeader($name, $value);
    }

    public function getRawHeaders(): ?string
    {
        return $this->getPage()->getRawHeaders();
    }

    public function setRawHeaders(?string $rawHeaders): void
    {
        $this->getPage()->setRawHeaders($rawHeaders);
    }

    public function getSite(): ?SiteInterface
    {
        return $this->getPage()->getSite();
    }

    public function setSite(?SiteInterface $site = null): void
    {
        $this->getPage()->setSite($site);
    }

    public function getEdited(): bool
    {
        return $this->getPage()->getEdited();
    }

    public function setEdited(bool $edited): void
    {
        $this->getPage()->setEdited($edited);
    }

    public function getSnapshots(): array
    {
        return $this->getPage()->getSnapshots();
    }

    public function setSnapshots(array $snapshots): void
    {
        $this->getPage()->setSnapshots($snapshots);
    }

    public function getSnapshot(): ?SnapshotInterface
    {
        return $this->getPage()->getSnapshot();
    }

    public function addSnapshot(SnapshotInterface $snapshot): void
    {
        $this->getPage()->addSnapshot($snapshot);
    }

    public function isError(): bool
    {
        return $this->getPage()->isError();
    }

    public function isHybrid(): bool
    {
        return $this->getPage()->isHybrid();
    }

    public function isDynamic(): bool
    {
        return $this->getPage()->isDynamic();
    }

    public function isCms(): bool
    {
        return $this->getPage()->isCms();
    }

    public function isInternal(): bool
    {
        return $this->getPage()->isInternal();
    }

    private function getPage(): PageInterface
    {
        if (null === $this->page) {
            $this->page = $this->transformer->load($this->snapshot);
        }

        return $this->page;
    }
}
