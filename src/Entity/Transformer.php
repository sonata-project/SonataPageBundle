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

namespace Sonata\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Serializer\BlockTypeExtractor;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * This class transform a SnapshotInterface into PageInterface.
 *
 * @phpstan-import-type PageContent from TransformerInterface
 * @phpstan-import-type BlockContent from TransformerInterface
 */
final class Transformer implements TransformerInterface
{
    /**
     * @var array<Collection<array-key, PageInterface>>
     */
    private array $children = [];

    /**
     * @param ManagerInterface<PageBlockInterface>                          $blockManager
     * @param SerializerInterface&NormalizerInterface&DenormalizerInterface $serializer
     */
    public function __construct(
        private SnapshotManagerInterface $snapshotManager,
        private PageManagerInterface $pageManager,
        private ManagerInterface $blockManager,
        private ManagerRegistry $registry,
        private ?SerializerInterface $serializer = null
    ) {
        // NEXT_MAJOR: Remove null support
        if (null === $this->serializer) {
            @trigger_error(sprintf(
                'Not passing an instance of %s as 5th parameter is deprecated since version 4.x and will be removed in 5.0.',
                SerializerInterface::class
            ), \E_USER_DEPRECATED);
        }
    }

    public function create(PageInterface $page, ?SnapshotInterface $snapshot = null): SnapshotInterface
    {
        $snapshot ??= $this->snapshotManager->create();

        $snapshot->setPage($page);
        $snapshot->setUrl($page->getUrl());
        $snapshot->setEnabled($page->getEnabled());
        $snapshot->setRouteName($page->getRouteName());
        $snapshot->setPageAlias($page->getPageAlias());
        $snapshot->setType($page->getType());
        $snapshot->setName($page->getName());
        $snapshot->setPosition($page->getPosition());
        $snapshot->setDecorate($page->getDecorate());

        if (null === $page->getSite()) {
            throw new \RuntimeException(sprintf('No site linked to the page.id=%s', $page->getId() ?? ''));
        }

        $snapshot->setSite($page->getSite());

        $parent = $page->getParent();
        if (null !== $parent) {
            $snapshot->setParentId($parent->getId());
        }

        // NEXT_MAJOR: Remove null support
        if (null !== $this->serializer) {
            /**
             * @var PageContent $content
             */
            $content = $this->serializer->normalize($page, null, [
                DateTimeNormalizer::FORMAT_KEY => 'U',
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                AbstractNormalizer::CALLBACKS => [
                    'blocks' => static fn (Collection $collection, PageInterface $object, string $attribute, ?string $format = null, array $context = []) => $collection->filter(static fn (BlockInterface $block) => !$block->hasParent())->getValues(),
                    'parent' => static fn (?PageInterface $page, PageInterface $object, string $attribute, ?string $format = null, array $context = []) => $page?->getId(),
                ],
            ]);
        } else {
            $blocks = [];
            foreach ($page->getBlocks() as $block) {
                if (null !== $block->getParent()) { // ignore block with a parent => must be a child of a main
                    continue;
                }

                $blocks[] = $this->createBlock($block);
            }

            $createdAt = $page->getCreatedAt();
            $updatedAt = $page->getUpdatedAt();
            $parent = $page->getParent();

            $data = [
                'id' => $page->getId(),
                'name' => $page->getName(),
                'javascript' => $page->getJavascript(),
                'stylesheet' => $page->getStylesheet(),
                'raw_headers' => $page->getRawHeaders(),
                'title' => $page->getTitle(),
                'meta_description' => $page->getMetaDescription(),
                'meta_keyword' => $page->getMetaKeyword(),
                'template_code' => $page->getTemplateCode(),
                'request_method' => $page->getRequestMethod(),
                'created_at' => $createdAt?->format('U'),
                'updated_at' => $updatedAt?->format('U'),
                'slug' => $page->getSlug(),
                'parent_id' => $parent?->getId(),
                'blocks' => $blocks,
            ];
            // need to filter out null values

            /**
             * @var PageContent $content
             */
            $content = array_filter($data, static fn ($v) => null !== $v);
        }

        $snapshot->setContent($content);

        return $snapshot;
    }

    public function load(SnapshotInterface $snapshot): PageInterface
    {
        $page = $this->pageManager->createWithDefaults();

        $page->setRouteName($snapshot->getRouteName());
        $page->setPageAlias($snapshot->getPageAlias());
        $page->setType($snapshot->getType());
        $page->setUrl($snapshot->getUrl());
        $page->setPosition($snapshot->getPosition());
        $page->setDecorate($snapshot->getDecorate());
        $page->setSite($snapshot->getSite());
        $page->setEnabled($snapshot->getEnabled());

        $content = $snapshot->getContent();

        $pageClass = $this->pageManager->getClass();

        // NEXT_MAJOR: Remove null support
        if (null !== $this->serializer) {
            $this->serializer->denormalize($content, $pageClass, null, [
                DateTimeNormalizer::FORMAT_KEY => 'U',
                AbstractNormalizer::OBJECT_TO_POPULATE => $page,
                AbstractNormalizer::CALLBACKS => $this->getDenormalizeCallbacks(),
            ]);
        } elseif (null !== $content) {
            $page->setId($content['id']);
            $page->setJavascript($content['javascript'] ?? null);
            $page->setStylesheet($content['stylesheet'] ?? null);
            $page->setRawHeaders($content['raw_headers'] ?? null);
            $page->setTitle($content['title'] ?? null);
            $page->setMetaDescription($content['meta_description'] ?? null);
            $page->setMetaKeyword($content['meta_keyword'] ?? null);

            $page->setName($content['name'] ?? null);
            $page->setSlug($content['slug'] ?? null);
            $page->setTemplateCode($content['template_code'] ?? null);
            $page->setRequestMethod($content['request_method'] ?? null);

            $createdAt = new \DateTime();
            $createdAt->setTimestamp((int) $content['created_at']);
            $page->setCreatedAt($createdAt);

            $updatedAt = new \DateTime();
            $updatedAt->setTimestamp((int) $content['updated_at']);
            $page->setUpdatedAt($updatedAt);
        }

        return $page;
    }

    public function loadBlock(array $content, PageInterface $page): PageBlockInterface
    {
        $block = $this->blockManager->create();

        $block->setPage($page);

        $blockClass = $this->blockManager->getClass();

        // NEXT_MAJOR: Remove null support
        if (null !== $this->serializer) {
            $this->serializer->denormalize($content, $blockClass, null, [
                DateTimeNormalizer::FORMAT_KEY => 'U',
                AbstractNormalizer::OBJECT_TO_POPULATE => $block,
                AbstractNormalizer::CALLBACKS => $this->getDenormalizeCallbacks(),
            ]);
        } else {
            if (isset($content['id'])) {
                $block->setId($content['id']);
            }

            if (isset($content['name'])) {
                $block->setName($content['name']);
            }

            // NEXT_MAJOR: Simplify this code by removing the in_array function and assign directly.
            $block->setEnabled(\in_array($content['enabled'], ['1', true], true));

            if (isset($content['position']) && is_numeric($content['position'])) {
                $block->setPosition((int) $content['position']);
            }

            $block->setSettings($content['settings']);

            if (isset($content['type'])) {
                $block->setType($content['type']);
            }

            $createdAt = new \DateTime();
            $createdAt->setTimestamp((int) $content['created_at']);
            $block->setCreatedAt($createdAt);

            $updatedAt = new \DateTime();
            $updatedAt->setTimestamp((int) $content['updated_at']);
            $block->setUpdatedAt($updatedAt);

            /**
             * @phpstan-var BlockContent $child
             */
            foreach ($content['blocks'] as $child) {
                $block->addChild($this->loadBlock($child, $page));
            }
        }

        return $block;
    }

    public function getChildren(PageInterface $page): Collection
    {
        $id = $page->getId();
        \assert(null !== $id);

        if (!isset($this->children[$id])) {
            $date = new \DateTime();
            $parameters = [
                'publicationDateStart' => $date,
                'publicationDateEnd' => $date,
                'parentId' => $id,
            ];

            $manager = $this->registry->getManagerForClass($this->snapshotManager->getClass());

            if (!$manager instanceof EntityManagerInterface) {
                throw new \RuntimeException('Invalid entity manager type');
            }

            $snapshots = $manager->createQueryBuilder()
                ->select('s')
                ->from($this->snapshotManager->getClass(), 's')
                ->where('s.parentId = :parentId and s.enabled = 1')
                ->andWhere('s.publicationDateStart <= :publicationDateStart AND ( s.publicationDateEnd IS NULL OR s.publicationDateEnd >= :publicationDateEnd )')
                ->orderBy('s.position')
                ->setParameters($parameters)
                ->getQuery()
                ->execute();

            /**
             * @var Collection<array-key, PageInterface>
             */
            $collection = new ArrayCollection();

            foreach ($snapshots as $snapshot) {
                $collection->add($this->snapshotManager->createSnapshotPageProxy($this, $snapshot));
            }

            $this->children[$id] = $collection;
        }

        return $this->children[$id];
    }

    /**
     * @return \Closure[]
     */
    private function getDenormalizeCallbacks(): array
    {
        $result = [
            'position' => static fn (string|int|null $value, string $object, string $attribute, ?string $format = null, array $context = []): int => null === $value ? 0 : (int) $value,
        ];

        $nullableStringCallback = static fn (?string $value, string $object, string $attribute, ?string $format = null, array $context = []): string => (string) $value;

        foreach (BlockTypeExtractor::NULLABLE_STRINGS as $key) {
            $result[$key] = $nullableStringCallback;
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     *
     * @phpstan-return BlockContent
     */
    private function createBlock(BlockInterface $block): array
    {
        $childBlocks = [];

        foreach ($block->getChildren() as $child) {
            $childBlocks[] = $this->createBlock($child);
        }

        /**
         * @var numeric-string|null $createdAt
         */
        $createdAt = $block->getCreatedAt()?->format('U');
        /**
         * @var numeric-string|null $updatedAt
         */
        $updatedAt = $block->getUpdatedAt()?->format('U');

        return [
            'id' => $block->getId(),
            'name' => $block->getName(),
            'enabled' => $block->getEnabled(),
            'position' => $block->getPosition(),
            'settings' => $block->getSettings(),
            'type' => $block->getType(),
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'blocks' => $childBlocks,
        ];
    }
}
