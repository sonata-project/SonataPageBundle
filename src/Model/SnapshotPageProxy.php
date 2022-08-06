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
final class SnapshotPageProxy implements SnapshotPageProxyInterface
{
    private SnapshotManagerInterface $manager;

    private TransformerInterface $transformer;

    private SnapshotInterface $snapshot;

    private ?PageInterface $page = null;

    /**
     * @var array<PageInterface>|null
     */
    private ?array $parents = null;

    public function __construct(
        SnapshotManagerInterface $manager,
        TransformerInterface $transformer,
        SnapshotInterface $snapshot
    ) {
        $this->manager = $manager;
        $this->snapshot = $snapshot;
        $this->transformer = $transformer;
    }

    /**
     * @return string
     */
    public function __toString()
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
     *
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'pageId' => $this->getPage()->getId(),
            'snapshotId' => $this->snapshot->getId(),
        ]);
    }

    /**
     * Unserialize a snapshot page proxy.
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
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

    public function getTitle()
    {
        return $this->getPage()->getTitle();
    }

    public function setTitle($title): void
    {
        $this->getPage()->setTitle($title);
    }

    public function getRouteName()
    {
        return $this->getPage()->getRouteName();
    }

    public function setRouteName($routeName): void
    {
        $this->getPage()->setRouteName($routeName);
    }

    public function getPageAlias()
    {
        return $this->getPage()->getPageAlias();
    }

    public function setPageAlias($pageAlias)
    {
        $this->getPage()->setPageAlias($pageAlias);
    }

    public function getType()
    {
        return $this->getPage()->getType();
    }

    public function setType($type): void
    {
        $this->getPage()->setType($type);
    }

    public function getEnabled()
    {
        return $this->getPage()->getEnabled();
    }

    public function setEnabled($enabled): void
    {
        $this->getPage()->setEnabled($enabled);
    }

    public function getName()
    {
        return $this->getPage()->getName();
    }

    public function setName($name): void
    {
        $this->getPage()->setName($name);
    }

    public function getSlug()
    {
        return $this->getPage()->getSlug();
    }

    public function setSlug($slug): void
    {
        $this->getPage()->setSlug($slug);
    }

    public function getUrl()
    {
        return $this->getPage()->getUrl();
    }

    public function setUrl($url): void
    {
        $this->getPage()->setUrl($url);
    }

    public function getCustomUrl()
    {
        return $this->getPage()->getCustomUrl();
    }

    public function setCustomUrl($customUrl): void
    {
        $this->getPage()->setCustomUrl($customUrl);
    }

    public function getMetaKeyword()
    {
        return $this->getPage()->getMetaKeyword();
    }

    public function setMetaKeyword($metaKeyword): void
    {
        $this->getPage()->setMetaKeyword($metaKeyword);
    }

    public function getMetaDescription()
    {
        return $this->getPage()->getMetaDescription();
    }

    public function setMetaDescription($metaDescription): void
    {
        $this->getPage()->setMetaDescription($metaDescription);
    }

    public function getJavascript()
    {
        return $this->getPage()->getJavascript();
    }

    public function setJavascript($javascript): void
    {
        $this->getPage()->setJavascript($javascript);
    }

    public function getStylesheet()
    {
        return $this->getPage()->getStylesheet();
    }

    public function setStylesheet($stylesheet): void
    {
        $this->getPage()->setStylesheet($stylesheet);
    }

    public function getCreatedAt()
    {
        return $this->getPage()->getCreatedAt();
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt = null): void
    {
        $this->getPage()->setCreatedAt($createdAt);
    }

    public function getUpdatedAt()
    {
        return $this->getPage()->getUpdatedAt();
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null): void
    {
        $this->getPage()->setUpdatedAt($updatedAt);
    }

    public function getChildren()
    {
        if (!$this->getPage()->getChildren()->count()) {
            $this->getPage()->setChildren(new SnapshotChildrenCollection($this->transformer, $this->getPage()));
        }

        return $this->getPage()->getChildren();
    }

    public function setChildren($children): void
    {
        $this->getPage()->setChildren($children);
    }

    public function addChild(PageInterface $child): void
    {
        $this->getPage()->addChild($child);
    }

    public function getBlocks()
    {
        if (!\count($this->getPage()->getBlocks())) {
            $content = $this->snapshot->getContent();

            if ($content) {
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

    public function getContainerByCode($code)
    {
        return $this->getPage()->getContainerByCode($code);
    }

    public function getBlocksByType($type)
    {
        return $this->getPage()->getBlocksByType($type);
    }

    public function getParent($level = -1)
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

    public function getParents()
    {
        if (!$this->parents) {
            $parents = [];

            $snapshot = $this->snapshot;

            while ($snapshot) {
                $content = $snapshot->getContent();

                if (!isset($content['parent_id'])) {
                    break;
                }

                $snapshot = $this->manager->findEnableSnapshot([
                    'pageId' => $content['parent_id'],
                ]);

                if (!$snapshot) {
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

    public function getTemplateCode()
    {
        return $this->getPage()->getTemplateCode();
    }

    public function setTemplateCode($templateCode): void
    {
        $this->getPage()->setTemplateCode($templateCode);
    }

    public function getDecorate()
    {
        return $this->getPage()->getDecorate();
    }

    public function setDecorate($decorate): void
    {
        $this->getPage()->setDecorate($decorate);
    }

    public function getPosition()
    {
        return $this->getPage()->getPosition();
    }

    public function setPosition($position): void
    {
        $this->getPage()->setPosition($position);
    }

    public function getRequestMethod()
    {
        return $this->getPage()->getRequestMethod();
    }

    public function setRequestMethod($method): void
    {
        $this->getPage()->setRequestMethod($method);
    }

    public function hasRequestMethod($method)
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

    public function addHeader($name, $value): void
    {
        $this->getPage()->addHeader($name, $value);
    }

    public function getRawHeaders()
    {
        return $this->getPage()->getRawHeaders();
    }

    public function setRawHeaders($rawHeaders): void
    {
        $this->getPage()->setRawHeaders($rawHeaders);
    }

    public function getSite()
    {
        return $this->getPage()->getSite();
    }

    public function setSite(?SiteInterface $site = null): void
    {
        $this->getPage()->setSite($site);
    }

    public function getEdited()
    {
        return $this->getPage()->getEdited();
    }

    public function setEdited($edited): void
    {
        $this->getPage()->setEdited($edited);
    }

    public function getSnapshots()
    {
        return $this->getPage()->getSnapshots();
    }

    public function setSnapshots($snapshots): void
    {
        $this->getPage()->setSnapshots($snapshots);
    }

    public function getSnapshot()
    {
        return $this->getPage()->getSnapshot();
    }

    public function addSnapshot(SnapshotInterface $snapshot): void
    {
        $this->getPage()->addSnapshot($snapshot);
    }

    public function isError()
    {
        return $this->getPage()->isError();
    }

    public function isHybrid()
    {
        return $this->getPage()->isHybrid();
    }

    public function isDynamic()
    {
        return $this->getPage()->isDynamic();
    }

    public function isCms()
    {
        return $this->getPage()->isCms();
    }

    public function isInternal()
    {
        return $this->getPage()->isInternal();
    }

    private function getPage(): PageInterface
    {
        if (!$this->page) {
            $this->page = $this->transformer->load($this->snapshot);
        }

        return $this->page;
    }
}
