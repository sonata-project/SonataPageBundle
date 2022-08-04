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
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\PageBundle\Entity\Transformer;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;

final class TransformerTest extends TestCase
{
    /**
     * @var MockObject|SnapshotManagerInterface
     */
    protected $snapshotManager;
    /**
     * @var MockObject|PageManagerInterface
     */
    protected $pageManager;
    /**
     * @var MockObject|BlockManagerInterface|ManagerInterface<PageBlockInterface>
     */
    protected $blockManager;

    protected TransformerInterface $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->snapshotManager = $this->createMock(SnapshotManagerInterface::class);

        $this->pageManager = $this->createMock(PageManagerInterface::class);
        $this->blockManager = $this->createMock(BlockManagerInterface::class);
        $registry = $this->createMock(ManagerRegistry::class);

        $this->transformer = new Transformer(
            $this->snapshotManager,
            $this->pageManager,
            $this->blockManager,
            $registry,
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

    /**
     * remove group legacy in next mayor.
     *
     * @group legacy
     */
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
        if (method_exists($block1, 'addChild')) {
            $block1->addChild($block2);
        } else {
            $block1->addChildren($block2);
        }

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
        if (method_exists($page, 'addBlock')) {
            $page->addBlock($block1);
            $page->addBlock($block2);
        } else {
            $page->addBlocks($block1);
            $page->addBlocks($block2);
        }
        if (method_exists($parentPage, 'addChild')) {
            $parentPage->addChild($page);
        } else {
            $parentPage->addChildren($page);
        }

        $snapshot = $this->transformer->create($page);
        static::assertSame($page->getUrl(), $snapshot->getUrl());
        static::assertSame($page->getName(), $snapshot->getName());

        static::assertSame($this->getTestContent($datetime), $snapshot->getContent());
    }

    /**
     * remove group legacy in next mayor.
     *
     * @group legacy
     */
    public function testLoadSnapshotToPage(): void
    {
        $method = method_exists($this->pageManager, 'createWithDefaults') ? 'createWithDefaults' : 'create';
        $this->pageManager->method($method)->willReturn(new SonataPagePage());
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

    public function testLoadBlock(): void
    {
        $this->blockManager->method('create')->willReturnCallback(static fn () => new SonataPageBlock());

        $dateTime = new \DateTime();

        $page = new SonataPagePage();

        $block = $this->transformer->loadBlock($this->getTestBlockArray($dateTime), $page);

        static::assertSame('block123', $block->getId());
    }

    protected function getTestContent(\DateTimeInterface $datetime): array
    {
        return [
            'id' => 'page_child',
            'name' => 'Page Child',
            'javascript' => null,
            'stylesheet' => null,
            'raw_headers' => null,
            'title' => 'Page Child Title',
            'meta_description' => null,
            'meta_keyword' => null,
            'template_code' => null,
            'request_method' => 'GET|POST|HEAD|DELETE|PUT',
            'created_at' => $datetime->format('U'),
            'updated_at' => $datetime->format('U'),
            'slug' => null,
            'parent_id' => 'page_parent',
            'target_id' => null, // REMOVE NEXT_MAYOR
            'blocks' => [
                $this->getTestBlockArray($datetime),
            ],
        ];
    }

    protected function getTestBlockArray(\DateTimeInterface $datetime): array
    {
        return [
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
        ];
    }
}
