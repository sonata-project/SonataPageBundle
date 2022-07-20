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
use Sonata\PageBundle\Model\Block;
use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\Page;
use Sonata\PageBundle\Model\PageInterface;

final class BlockInteractorTest extends TestCase
{
    /**
     * @testdox It is returning a block list.
     */
    public function testLoadPageBlocks(): void
    {
        //Mock
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $blockManagerInterfaceMock = $this->createMock(BlockManagerInterface::class);
        //NEXT_MAJOR: use PageInterface
        $pageMock = $this->createMock(Page::class);
        //NEXT_MAJOR: use BlockInterface
        $blockMock = $this->createMock(Block::class);

        $blockInteractorMock = $this
            ->getMockBuilder(BlockInteractor::class)
            ->setConstructorArgs([$managerRegistryMock, $blockManagerInterfaceMock])
            ->onlyMethods(['getBlocksById'])
            ->getMock();

        $blockInteractorMock
            ->expects(static::once())
            ->method('getBlocksById')
            ->willReturn([$blockMock]);

        //Run
        $blocks = $blockInteractorMock->loadPageBlocks($pageMock);

        //Asserts
        static::assertSame([$blockMock], $blocks);
    }

    /**
     * @testdox it'll return an empty array for blocks that are already loaded.
     */
    public function testNotLoadBlocks(): void
    {
        //Mock
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $blockManagerInterfaceMock = $this->createMock(BlockManagerInterface::class);
        $pageMock = $this->createMock(PageInterface::class);
        $pageMock
            ->expects(static::once())
            ->method('getId')
            ->willReturn(1);

        $blockInteractor = new BlockInteractor($managerRegistryMock, $blockManagerInterfaceMock);

        //Change property visibility
        $reflection = new \ReflectionClass($blockInteractor);
        $reflection_property = $reflection->getProperty('pageBlocksLoaded');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($blockInteractor, [1 => 'fake_value(block already loaded).']);

        //Run
        $result = $blockInteractor->loadPageBlocks($pageMock);

        //Assert
        static::assertSame([], $result);
    }

    /**
     * @testdox It's adding a new block children and "disableChildrenLazyLoading"
     *
     * I'm using "containerBlock" and "emailButtonBlock", just for the test be more clear,
     *  because usually the container block is the parent of others blocks in the page.
     */
    public function testDisableAndAddChildrenBlocks(): void
    {
        //Mock
        $managerRegistryMock = $this->createMock(ManagerRegistry::class);
        $blockManagerInterfaceMock = $this->createMock(BlockManagerInterface::class);

        //NEXT_MAJOR: use PageInterface
        $pageMock = $this->createMock(Page::class);
        $containerBlockMock = $this->createMock(Block::class);
        $containerBlockMock
            ->expects(static::exactly(2))//NEXT_MAJOR: change this to static::once()
            ->method('getId')
            ->willReturn(22);

        $emailButtonMock = $this->createMock(Block::class);
        $emailButtonMock
            ->expects(static::exactly(3))//NEXT_MAJOR: change this to static::once()
            ->method('getParent')
            ->willReturn($containerBlockMock);

        //Run
        $blockInteractorMock = $this
            ->getMockBuilder(BlockInteractor::class)
            ->setConstructorArgs([$managerRegistryMock, $blockManagerInterfaceMock])
            ->onlyMethods(['getBlocksById'])
            ->getMock();
        $blockInteractorMock
            ->expects(static::once())
            ->method('getBlocksById')
            ->willReturn([22 => $containerBlockMock, $emailButtonMock]);

        //Assert
        $blockInteractorMock->loadPageBlocks($pageMock);
    }
}
