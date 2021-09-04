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

namespace Sonata\PageBundle\Tests\Command;

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Command\CreateBlockContainerCommand;
use Sonata\PageBundle\Entity\BlockInteractor;
use Sonata\PageBundle\Entity\BlockManager;
use Sonata\PageBundle\Entity\PageManager;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateBlockContainerCommandTest extends TestCase
{
    /**
     * @var Stub&BlockInteractor
     */
    protected $blockInteractor;

    /**
     * @var Stub&PageManager
     */
    protected $pageManager;

    /**
     * @var Stub&BlockManager
     */
    protected $blockManager;

    /**
     * @var Stub&ContainerInterface
     */
    protected $container;

    protected function setUp(): void
    {
        $this->blockInteractor = $this->createStub(BlockInteractor::class);
        $this->pageManager = $this->createStub(PageManager::class);
        $this->blockManager = $this->createStub(BlockManager::class);
        $this->container = $this->createStub(ContainerInterface::class);

        $this->container->method('get')->willReturnMap([
            ['sonata.page.block_interactor', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->blockInteractor],
            ['sonata.page.manager.page', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->pageManager],
            ['sonata.page.manager.block', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->blockManager],
        ]);
    }

    /**
     * Tests that Block is added into Page's blocks field.
     */
    public function testCreateBlock(): void
    {
        $block = $this->createStub(PageBlockInterface::class);
        $this->blockInteractor->method('createNewContainer')->willReturn($block);

        $page = new Page();
        $this->pageManager->method('findBy')->with(['templateCode' => 'foo'])->willReturn([$page]);
        $this->pageManager->method('save')->with($page)->willReturn($page);

        $command = new CreateBlockContainerCommand();
        $command->setContainer($this->container);

        $input = $this->createStub(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['templateCode', 'foo'],
            ['blockCode', 'content_bar'],
            ['blockName', 'Baz!'],
        ]);

        $output = $this->createStub(OutputInterface::class);

        $method = new \ReflectionMethod($command, 'execute');
        $method->setAccessible(true);
        $method->invoke($command, $input, $output);

        static::assertSame($page->getBlocks(), [$block]);
    }
}
