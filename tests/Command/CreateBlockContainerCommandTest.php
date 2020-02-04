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

namespace Sonata\Tests\PageBundle\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
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
     * @var ObjectProphecy|BlockInteractor
     */
    protected $blockInteractor;

    /**
     * @var ObjectProphecy|PageManager
     */
    protected $pageManager;

    /**
     * @var ObjectProphecy|BlockManager
     */
    protected $blockManager;

    /**
     * @var ObjectProphecy|ContainerInterface
     */
    protected $container;

    protected function setUp(): void
    {
        $this->blockInteractor = $this->prophesize(BlockInteractor::class);

        $this->pageManager = $this->prophesize(PageManager::class);

        $this->blockManager = $this->prophesize(BlockManager::class);

        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get('sonata.page.block_interactor')->willReturn($this->blockInteractor);
        $this->container->get('sonata.page.manager.page')->willReturn($this->pageManager);
        $this->container->get('sonata.page.manager.block')->willReturn($this->blockManager);
    }

    /**
     * Tests that Block is added into Page's blocks field.
     */
    public function testCreateBlock(): void
    {
        $block = $this->prophesize(PageBlockInterface::class);
        $this->blockInteractor->createNewContainer(Argument::any())->willReturn($block->reveal());

        $page = new Page();
        $this->pageManager->findBy(['templateCode' => 'foo'])->willReturn([$page]);
        $this->pageManager->save($page)->willReturn($page);

        $command = new CreateBlockContainerCommand();
        $command->setContainer($this->container->reveal());

        $input = $this->prophesize(InputInterface::class);
        $input->getArgument('templateCode')->willReturn('foo');
        $input->getArgument('blockCode')->willReturn('content_bar');
        $input->getArgument('blockName')->willReturn('Baz!');

        $output = $this->prophesize(OutputInterface::class);

        $method = new \ReflectionMethod($command, 'execute');
        $method->setAccessible(true);
        $method->invoke($command, $input->reveal(), $output->reveal());

        $this->assertSame($page->getBlocks(), [$block->reveal()]);
    }
}
