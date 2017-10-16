<?php

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
use Sonata\PageBundle\Tests\Model\Page;
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

    public function setUp()
    {
        $this->blockInteractor = $this->prophesize('Sonata\PageBundle\Entity\BlockInteractor');

        $this->pageManager = $this->prophesize('Sonata\PageBundle\Entity\PageManager');

        $this->blockManager = $this->prophesize('Sonata\PageBundle\Entity\BlockManager');

        $this->container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->container->get('sonata.page.block_interactor')->willReturn($this->blockInteractor);
        $this->container->get('sonata.page.manager.page')->willReturn($this->pageManager);
        $this->container->get('sonata.page.manager.block')->willReturn($this->blockManager);
    }

    /**
     * Tests that Block is added into Page's blocks field.
     */
    public function testCreateBlock()
    {
        $block = $this->prophesize('Sonata\PageBundle\Model\PageBlockInterface');
        $this->blockInteractor->createNewContainer(Argument::any())->willReturn($block->reveal());

        $page = new Page();
        $this->pageManager->findBy(['templateCode' => 'foo'])->willReturn([$page]);
        $this->pageManager->save($page)->willReturn($page);

        $command = new CreateBlockContainerCommand();
        $command->setContainer($this->container->reveal());

        $input = $this->prophesize('Symfony\Component\Console\Input\InputInterface');
        $input->getArgument('templateCode')->willReturn('foo');
        $input->getArgument('blockCode')->willReturn('content_bar');
        $input->getArgument('blockName')->willReturn('Baz!');

        $output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');

        $method = new \ReflectionMethod($command, 'execute');
        $method->setAccessible(true);
        $method->invoke($command, $input->reveal(), $output->reveal());

        $this->assertEquals($page->getBlocks(), [$block->reveal()]);
    }
}
