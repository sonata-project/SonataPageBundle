<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Entity;

use Symfony\Bundle\DoctrineBundle\Registry;

use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Tests\Model\Block;
use Sonata\PageBundle\Entity\BlockInteractor;

/**
 * BlockInteractorTest class
 *
 * This is the BlockInteractor test class
 *
 * @author Vincent Composieux <composieux@ekino.com>
 */
class BlockInteractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test createNewContainer() method with some values
     */
    public function testCreateNewContainer()
    {
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->disableOriginalConstructor()->getMock();

        $blockManager = $this->getMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->any())->method('create')->will($this->returnValue(new Block()));

        $blockInteractor = new BlockInteractor($registry, $blockManager);

        $container = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'code'    => 'my-code'
        ), function ($container) {
            $container->setSetting('layout', '<div class="custom-layout">{{ CONTENT }}</div>');
        });

        $this->assertInstanceOf('\Sonata\BlockBundle\Model\BlockInterface', $container);

        $settings = $container->getSettings();

        $this->assertTrue($container->getEnabled());

        $this->assertEquals('my-code', $settings['code']);
        $this->assertEquals('<div class="custom-layout">{{ CONTENT }}</div>', $settings['layout']);
    }

    /**
     * Test createNewBlock() method with some values
     */
    public function testCreateNewBlock()
    {
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->disableOriginalConstructor()->getMock();

        $blockManager = $this->getMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->any())->method('create')->will($this->returnValue(new Block()));

        $blockInteractor = new BlockInteractor($registry, $blockManager);

        $container = new Block();
        $container->setName('my.custom.container');
        $container->setType('sonata.page.block.container');

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $block = $blockInteractor->createNewBlock('my.custom.text.block', $container, array(
            'type' => 'sonata.block.service.text',
            'page' => $page,
        ), function ($container) {
            $container->setSetting('layout', '<div class="custom-layout">{{ CONTENT }}</div>');
        });

        $this->assertInstanceOf('\Sonata\BlockBundle\Model\BlockInterface', $block);

        $settings = $block->getSettings();

        $this->assertTrue($block->getEnabled());

        $this->assertEquals('my.custom.text.block', $block->getName());
        $this->assertEquals('sonata.block.service.text', $block->getType());
        $this->assertEquals($container, $block->getParent());
        $this->assertEquals($page, $block->getPage());

        $this->assertEquals('<div class="custom-layout">{{ CONTENT }}</div>', $settings['layout']);
    }
}
