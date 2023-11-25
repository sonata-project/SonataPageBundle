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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;

/**
 * @phpstan-import-type PageContent from TransformerInterface
 * @phpstan-import-type BlockContent from TransformerInterface
 */
abstract class TransformerTest extends TestCase
{
    /**
     * @var MockObject&SnapshotManagerInterface
     */
    protected SnapshotManagerInterface $snapshotManager;

    /**
     * @var MockObject&PageManagerInterface
     */
    protected PageManagerInterface $pageManager;

    /**
     * @var MockObject&ManagerInterface<PageBlockInterface>
     */
    protected ManagerInterface $blockManager;

    protected TransformerInterface $transformer;

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

        $this->transformer = $this->setUpTransformer();
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

    public function testTransformerPageToSnapshotRequiredField(): void
    {
        $this->snapshotManager->method('create')->willReturn(new SonataPageSnapshot());
        $this->snapshotManager->method('getClass')->willReturn(SonataPageSnapshot::class);

        $datetime = new \DateTime();

        $site = new SonataPageSite();

        $page = new SonataPagePage();
        $page->setSite($site);
        $page->setName('Page Foo');
        $snapshot = $this->transformer->create($page);
        static::assertSame([
            'id' => null,
            'created_at' => null,
            'updated_at' => null,
            'name' => 'Page Foo',
            'request_method' => 'GET|POST|HEAD|DELETE|PUT',
            'blocks' => [],
        ], $snapshot->getContent());
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
     * @dataProvider provideLoadBlockCases
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
    public function provideLoadBlockCases(): iterable
    {
        $datetime = new \DateTime();

        /**
         * @var numeric-string $dateTimeString
         */
        $dateTimeString = $datetime->format('U');

        // Normal block
        yield [[
            'id' => 1,
            'name' => 'block1',
            'enabled' => true,
            'position' => 0,
            'settings' => [],
            'type' => 'type',
            'created_at' => $dateTimeString,
            'updated_at' => $dateTimeString,
            'blocks' => [[
                'id' => 2,
                'name' => 'block2',
                'enabled' => true,
                'position' => 1,
                'settings' => [],
                'type' => 'type',
                'created_at' => $dateTimeString,
                'updated_at' => $dateTimeString,
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
            'created_at' => (int) $dateTimeString,
            'updated_at' => (int) $dateTimeString,
            'blocks' => [[
                'id' => 2,
                'name' => 'block2',
                'enabled' => true,
                'position' => 1,
                'settings' => [],
                'type' => 'type',
                'created_at' => (int) $dateTimeString,
                'updated_at' => (int) $dateTimeString,
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

    abstract protected function setUpTransformer(): TransformerInterface;

    /**
     * @phpstan-return PageContent
     */
    private function getTestContent(\DateTimeInterface $datetime): array
    {
        /**
         * @var numeric-string $dateTimeString
         */
        $dateTimeString = $datetime->format('U');

        return [
            'id' => 'page_child',
            'name' => 'Page Child',
            'title' => 'Page Child Title',
            'request_method' => 'GET|POST|HEAD|DELETE|PUT',
            'created_at' => $dateTimeString,
            'updated_at' => $dateTimeString,
            'parent_id' => 'page_parent',
            'blocks' => [
                [
                    'id' => 'block123',
                    'name' => 'block1',
                    'enabled' => false,
                    'position' => 0,
                    'settings' => [],
                    'type' => 'type',
                    'created_at' => $dateTimeString,
                    'updated_at' => $dateTimeString,
                    'blocks' => [
                        [
                            'id' => 'block234',
                            'name' => 'block2',
                            'enabled' => false,
                            'position' => 0,
                            'settings' => [],
                            'type' => 'type',
                            'created_at' => $dateTimeString,
                            'updated_at' => $dateTimeString,
                            'blocks' => [],
                        ],
                    ],
                ],
            ],
        ];
    }
}
