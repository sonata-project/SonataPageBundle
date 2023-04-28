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
        private $serializer
    ) {
        $this->serializer = $serializer;
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

        /**
         * @var PageContent $content
         */
        $content = $this->serializer->normalize($page, null, [
            DateTimeNormalizer::FORMAT_KEY => 'U',
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
            AbstractNormalizer::CALLBACKS => [
                'blocks' => static fn (Collection $innerObject, PageInterface $outerObject, string $attributeName, ?string $format = null, array $context = []) => $innerObject->filter(static fn (BlockInterface $block) => !$block->hasParent())->getValues(),
                'parent' => static fn (?PageInterface $innerObject, PageInterface $outerObject, string $attributeName, ?string $format = null, array $context = []) => $innerObject instanceof PageInterface ? $innerObject->getId() : $innerObject,
            ],
        ]);

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

        $this->serializer->denormalize($content, $pageClass, null, [
            DateTimeNormalizer::FORMAT_KEY => 'U',
            AbstractNormalizer::OBJECT_TO_POPULATE => $page,
            AbstractNormalizer::CALLBACKS => $this->getDenormalizeCallbacks(),
        ]);

        return $page;
    }

    public function loadBlock(array $content, PageInterface $page): PageBlockInterface
    {
        $block = $this->blockManager->create();

        $block->setPage($page);

        $blockClass = $this->blockManager->getClass();

        $this->serializer->denormalize($content, $blockClass, null, [
            DateTimeNormalizer::FORMAT_KEY => 'U',
            AbstractNormalizer::OBJECT_TO_POPULATE => $block,
            AbstractNormalizer::CALLBACKS => $this->getDenormalizeCallbacks(),
        ]);

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
            'position' => static fn (string|int|null $innerObject, string $outerObject, string $attributeName, ?string $format = null, array $context = []): int => null === $innerObject ? 0 : (int) $innerObject,
        ];

        $nullableStringCallback = static fn (?string $innerObject, string $outerObject, string $attributeName, ?string $format = null, array $context = []): string => (string) $innerObject;

        foreach (BlockTypeExtractor::NULLABLE_STRINGS as $key) {
            $result[$key] = $nullableStringCallback;
        }

        return $result;
    }
}
