<?php

namespace Sonata\Test\PageBundle\Command;

use Sonata\PageBundle\Command\CreateBlockContainerCommand;
use Sonata\PageBundle\Tests\Model\Block;
use Sonata\PageBundle\Tests\Model\Page;

class CreateBlockCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that Block is added into Page's blocks field
     */
    public function testCreateBlock()
    {
        /** @var CreateBlockContainerCommand $command */
        $command = $this->getMockBuilder('Sonata\PageBundle\Command\CreateBlockContainerCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('getBlockInteractor', 'getPageManager', 'getBlockManager'))
            ->getMock();

        $blockInteractor = $this->getMockBuilder('Sonata\PageBundle\Entity\BlockInteractor')
            ->disableOriginalConstructor()
            ->setMethods(array('createNewContainer'))
            ->getMock();

        $container = new Block();
        $blockInteractor->method('createNewContainer')->willReturn($container);

        $command->method('getBlockInteractor')->willReturn($blockInteractor);

        $pageManager = $this->getMockBuilder('Sonata\PageBundle\Entity\PageManager')
            ->disableOriginalConstructor()
            ->setMethods(array('findBy', 'save'))
            ->getMock();

        $page = new Page();
        $pageManager->method('findBy')->willReturn(array($page));
        $pageManager->method('save')->willReturn(null);

        $command->method('getPageManager')->willReturn($pageManager);

        $blockManager = $this->getMockBuilder('Sonata\PageBundle\Entity\BlockManager')
            ->disableOriginalConstructor()
            ->getMock();

        $command->method('getBlockManager')->willReturn($blockManager);

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input->method('getArgument')->willReturn(null);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $method = new \ReflectionMethod($command, 'execute');
        $method->setAccessible(true);

        $method->invoke($command, $input, $output);

        $this->assertEquals($page->getBlocks(), array($container));
    }
}