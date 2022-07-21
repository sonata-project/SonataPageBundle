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
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BlockInteractor;
use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;

final class BlockInteractorTest extends TestCase
{
    /**
     * @testdox It is returning a block list.
     */
    public function testLoadPageBlocks(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $blockManager = $this->createMock(BlockManagerInterface::class);
        $page = $this->createMock(PageInterface::class);
        $block = $this->createMock(PageBlockInterface::class);

        $blockInteractor = new BlockInteractor($managerRegistry, $blockManager);

        $blockInteractor->expects(static::once())->method('getBlocksById')
            ->willReturn([$block]);

        $blocks = $blockInteractor->loadPageBlocks($page);

        static::assertSame([$block], $blocks);
    }

    /**
     * @testdox it'll return an empty array for blocks that are already loaded.
     */
    public function testNotLoadBlocks(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $blockManager = $this->createMock(BlockManagerInterface::class);
        $page = $this->createMock(PageInterface::class);

        $page->expects(static::once())->method('getId')->willReturn(1);

        $blockInteractor = new BlockInteractor($managerRegistry, $blockManager);

        // $reflection = new \ReflectionClass($blockInteractor);
        // $reflectionProperty = $reflection->getProperty('pageBlocksLoaded');
        // $reflectionProperty->setAccessible(true);
        // $reflectionProperty->setValue($blockInteractor, [1 => 'fake_value(block already loaded).']);

        $result = $blockInteractor->loadPageBlocks($page);

        static::assertSame([], $result);
    }

    /**
     * @testdox It's adding a new block children and "disableChildrenLazyLoading"
     *
     * I'm using "containerBlock" and "emailButton", just for the test be more clear,
     * because usually the container block is the parent of others blocks in the page.
     */
    public function testDisableAndAddChildrenBlocks(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $blockManager = $this->createMock(BlockManagerInterface::class);
        $page = $this->createMock(PageInterface::class);
        $containerBlock = $this->createMock(PageBlockInterface::class);
        $emailButton = $this->createMock(PageBlockInterface::class);

        $containerBlock->expects(static::once())->method('getId')->willReturn(22);
        $emailButton->expects(static::once())->method('getParent')->willReturn($containerBlock);

        $blockInteractor = new BlockInteractor($managerRegistry, $blockManager);

        $blockInteractor->expects(static::once())->method('getBlocksById')
            ->willReturn([22 => $containerBlock, $emailButton]);

        $blockInteractor->loadPageBlocks($page);
    }
}
