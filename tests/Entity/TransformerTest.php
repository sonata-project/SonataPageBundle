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

namespace Sonata\PageBundle\Tests\Entity;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\PageBundle\Entity\Transformer;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Serializer\BlockTypeExtractor;
use Sonata\PageBundle\Serializer\InterfaceTypeExtractor;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\Mapping\Loader\XmlFileLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @phpstan-import-type PageContent from TransformerInterface
 * @phpstan-import-type BlockContent from TransformerInterface
 */
final class TransformerTest extends TestCase
{
    /**
     * @var MockObject&SnapshotManagerInterface
     */
    private SnapshotManagerInterface $snapshotManager;

    /**
     * @var MockObject&PageManagerInterface
     */
    private PageManagerInterface $pageManager;

    /**
     * @var MockObject&ManagerInterface<PageBlockInterface>
     */
    private ManagerInterface $blockManager;

    private TransformerInterface $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshotManager = $this->createMock(SnapshotManagerInterface::class);

        $this->pageManager = $this->createMock(PageManagerInterface::class);
        $this->pageManager->method('createWithDefaults')->willReturn(new SonataPagePage());
        $this->pageManager->method('getClass')->willReturn(SonataPagePage::class);

        $this->blockManager = $this->createMock(ManagerInterface::class);
        $this->blockManager->method('create')->willReturnCallback(static fn () => new SonataPageBlock());
        $this->blockManager->method('getClass')->willReturn(SonataPageBlock::class);

        $registry = $this->createMock(ManagerRegistry::class);

        $loaders = new LoaderChain([
            new XmlFileLoader(__DIR__.'/../../src/Resources/config/serialization/Model.Block.xml'),
            new XmlFileLoader(__DIR__.'/../../src/Resources/config/serialization/Model.Page.xml'),
        ]);

        $classMetadataFactory = new ClassMetadataFactory($loaders);
        $nameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $extractor = new PropertyInfoExtractor([], [
            new BlockTypeExtractor($this->blockManager),
            new InterfaceTypeExtractor(
                $this->pageManager,
                $this->blockManager,
            ),
            new ReflectionExtractor(),
        ]);

        $objectNormalizer = new ObjectNormalizer($classMetadataFactory, $nameConverter, null, $extractor);

        $encoders = [new JsonEncoder()];
        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            $objectNormalizer,
        ];
        $serializer = new Serializer($normalizers, $encoders);

        $this->transformer = new Transformer(
            $this->snapshotManager,
            $this->pageManager,
            $this->blockManager,
            $registry,
            $serializer,
        );
    }

    public function testAssertExceptionCreateOnPageWithoutSite(): void
    {
        $this->snapshotManager->method('create')->willReturn(new SonataPageSnapshot());
        $this->snapshotManager->method('getClass')->willReturn(SonataPageSnapshot::class);

        $datetime = new \DateTime();

        $page = new SonataPagePage();
        $page->setId(123);
        $page->setUrl('/get-post');
        $page->setCreatedAt($datetime);
        $page->setUpdatedAt($datetime);

        $this->expectException(\RuntimeException::class);
        $this->transformer->create($page);
    }

    public function testTransformerPageToSnapshot(): void
    {
        $this->snapshotManager->method('create')->willReturn(new SonataPageSnapshot());
        $this->snapshotManager->method('getClass')->willReturn(SonataPageSnapshot::class);

        $datetime = new \DateTime();

        $site = new SonataPageSite();

        $block1 = new SonataPageBlock();
        $block1->setId('block123');
        $block1->setName('block1');
        $block1->setType('type');
        $block1->setPosition(0);
        $block1->setCreatedAt($datetime);
        $block1->setUpdatedAt($datetime);

        $block2 = new SonataPageBlock();
        $block2->setId('block234');
        $block2->setName('block2');
        $block2->setType('type');
        $block2->setPosition(0);
        $block2->setCreatedAt($datetime);
        $block2->setUpdatedAt($datetime);

        $block1->addChild($block2);

        $parentPage = new SonataPagePage();
        $parentPage->setId('page_parent');
        $parentPage->setName('Page Parent');
        $parentPage->setUrl('/get-parent');
        $parentPage->setSite($site);
        $parentPage->setCreatedAt($datetime);
        $parentPage->setUpdatedAt($datetime);

        $page = new SonataPagePage();
        $page->setId('page_child');
        $page->setName('Page Child');
        $page->setTitle('Page Child Title');
        $page->setUrl('/get-child');
        $page->setSite($site);
        $page->setCreatedAt($datetime);
        $page->setUpdatedAt($datetime);
        $page->addBlock($block1);
        $page->addBlock($block2);
        $parentPage->addChild($page);

        $snapshot = $this->transformer->create($page);

        static::assertSame($page->getUrl(), $snapshot->getUrl());
        static::assertSame($page->getName(), $snapshot->getName());
        static::assertSame($this->getTestContent($datetime), $snapshot->getContent());
    }

    public function testLoadSnapshotToPage(): void
    {
        $this->pageManager->method('createWithDefaults')->willReturn(new SonataPagePage());
        $this->pageManager->method('getClass')->willReturn(SonataPagePage::class);

        $dateTime = new \DateTime();
        $snapshot = new SonataPageSnapshot();
        $snapshot->setContent($this->getTestContent($dateTime));
        $snapshot->setUrl('/get-child');

        $page = $this->transformer->load($snapshot);

        static::assertSame('page_child', $page->getId());
        static::assertSame('Page Child', $page->getName());
        static::assertSame('Page Child Title', $page->getTitle());
        static::assertSame('/get-child', $page->getUrl());
    }

    /**
     * @dataProvider blockProvider
     *
     * @param array<string, mixed> $content
     *
     * @phpstan-param BlockContent $content
     */
    public function testLoadBlock(array $content): void
    {
        $this->blockManager->method('create')->willReturnCallback(static fn () => new SonataPageBlock());

        $block = $this->transformer->loadBlock($content, new SonataPagePage());

        static::assertSame($content['id'], $block->getId());
    }

    /**
     * @phpstan-return iterable<array{BlockContent}>
     */
    public function blockProvider(): iterable
    {
        $datetime = new \DateTime();

        // Normal block
        yield [[
            'id' => 1,
            'name' => 'block1',
            'enabled' => true,
            'position' => 0,
            'settings' => [],
            'type' => 'type',
            'created_at' => $datetime->format('U'),
            'updated_at' => $datetime->format('U'),
            'blocks' => [[
                'id' => 2,
                'name' => 'block2',
                'enabled' => true,
                'position' => 1,
                'settings' => [],
                'type' => 'type',
                'created_at' => $datetime->format('U'),
                'updated_at' => $datetime->format('U'),
                'blocks' => [],
            ]],
        ]];

        // Normal block but Int Datetime
        yield [[
            'id' => 2,
            'name' => 'block1',
            'enabled' => true,
            'position' => 0,
            'settings' => [],
            'type' => 'type',
            'created_at' => (int) $datetime->format('U'),
            'updated_at' => (int) $datetime->format('U'),
            'blocks' => [[
                'id' => 2,
                'name' => 'block2',
                'enabled' => true,
                'position' => 1,
                'settings' => [],
                'type' => 'type',
                'created_at' => (int) $datetime->format('U'),
                'updated_at' => (int) $datetime->format('U'),
                'blocks' => [],
            ]],
        ]];

        // Minimal block block
        yield [[
            'id' => null,
            'enabled' => false,
            'position' => null,
            'settings' => [],
            'type' => null,
            'created_at' => null,
            'updated_at' => null,
            'blocks' => [],
        ]];

        // Weird block data
        yield [[
            'id' => 'random_string',
            'name' => 'block1',
            'enabled' => '1',
            'position' => '',
            'settings' => [],
            'type' => 'type',
            'created_at' => null,
            'updated_at' => null,
            'blocks' => [[
                'id' => null,
                'enabled' => '0',
                'position' => null,
                'settings' => [],
                'type' => null,
                'created_at' => null,
                'updated_at' => null,
                'blocks' => [],
            ]],
        ]];

        // Numeric string position block data
        yield [[
            'id' => 'block123',
            'enabled' => false,
            'position' => '0',
            'settings' => [],
            'type' => null,
            'created_at' => null,
            'updated_at' => null,
            'blocks' => [],
        ]];
    }

    /**
     * @phpstan-return PageContent
     */
    private function getTestContent(\DateTimeInterface $datetime): array
    {
        return [
            'id' => 'page_child',
            'name' => 'Page Child',
//            'javascript' => null,
//            'stylesheet' => null,
//            'raw_headers' => null,
            'title' => 'Page Child Title',
//            'meta_description' => null,
//            'meta_keyword' => null,
//            'template_code' => null,
            'request_method' => 'GET|POST|HEAD|DELETE|PUT',
            'created_at' => $datetime->format('U'),
            'updated_at' => $datetime->format('U'),
//            'slug' => null,
            'parent_id' => 'page_parent',
            'blocks' => [
                [
                    'id' => 'block123',
                    'name' => 'block1',
                    'enabled' => false,
                    'position' => 0,
                    'settings' => [],
                    'type' => 'type',
                    'created_at' => $datetime->format('U'),
                    'updated_at' => $datetime->format('U'),
                    'blocks' => [
                        [
                            'id' => 'block234',
                            'name' => 'block2',
                            'enabled' => false,
                            'position' => 0,
                            'settings' => [],
                            'type' => 'type',
                            'created_at' => $datetime->format('U'),
                            'updated_at' => $datetime->format('U'),
                            'blocks' => [],
                        ],
                    ],
                ],
            ],
        ];
    }
}
