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

namespace Sonata\PageBundle\Tests\CmsManager;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\CmsManager\CmsSnapshotManager;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\Block;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\Model\Page;

final class SnapshotBlock extends Block
{
}

final class CmsSnapshotManagerTest extends TestCase
{
    private CmsSnapshotManager $manager;

    /**
     * @var MockObject&SnapshotManagerInterface
     */
    private SnapshotManagerInterface $snapshotManager;

    /**
     * @var MockObject&TransformerInterface
     */
    private TransformerInterface $transformer;

    /**
     * Setup manager object to test.
     */
    protected function setUp(): void
    {
        $this->snapshotManager = $this->createMock(SnapshotManagerInterface::class);
        $this->transformer = $this->createMock(TransformerInterface::class);
        $this->manager = new CmsSnapshotManager($this->snapshotManager, $this->transformer);
    }

    /**
     * Test finding an existing container in a page.
     */
    public function testFindExistingContainer(): void
    {
        $block = new SnapshotBlock();
        $block->setSettings(['code' => 'findme']);

        $page = new Page();
        $page->addBlock($block);

        $container = $this->manager->findContainer('findme', $page);

        static::assertNotNull($container);
        static::assertSame(
            spl_object_hash($block),
            spl_object_hash($container),
            'should retrieve the block of the page'
        );
    }

    /**
     * Test finding an non-existing container in a page does NOT create a new block.
     */
    public function testFindNonExistingContainerCreatesNoNewBlock(): void
    {
        $page = new Page();

        $container = $this->manager->findContainer('newcontainer', $page);

        static::assertNull($container, 'should not create a new container block');
    }

    public function testGetPageWithUnknownPage(): void
    {
        $this->expectException(PageNotFoundException::class);

        $this->snapshotManager->expects(static::once())->method('findEnableSnapshot')->willReturn(null);

        $site = $this->createMock(SiteInterface::class);

        $snapshotManager = new CmsSnapshotManager($this->snapshotManager, $this->transformer);

        $snapshotManager->getPage($site, 1);
    }

    public function testGetPageWithId(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $page = $this->createMock(SnapshotPageProxyInterface::class);
        $snapshot = $this->createMock(SnapshotInterface::class);

        $cBlock = new SonataPageBlock();
        $cBlock->setId(2);

        $pBlock = new SonataPageBlock();
        $pBlock->addChild($cBlock);
        $pBlock->setId(1);

        $page->method('getId')->willReturn(42);
        $page->method('getBlocks')->willReturn(new ArrayCollection([$pBlock]));

        $this->snapshotManager
            ->expects(static::once())
            ->method('findEnableSnapshot')
            ->willReturn($snapshot);

        $this->snapshotManager
            ->expects(static::once())
            ->method('createSnapshotPageProxy')
            ->willReturn($page);

        $page = $this->manager->getPage($site, 1);

        static::assertInstanceOf(SnapshotPageProxyInterface::class, $page);
        static::assertInstanceOf(PageBlockInterface::class, $this->manager->getBlock(1));
        static::assertInstanceOf(PageBlockInterface::class, $this->manager->getBlock(2));
    }
}
