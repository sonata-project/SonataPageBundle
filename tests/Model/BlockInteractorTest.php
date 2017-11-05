<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Model\Block;
use Sonata\PageBundle\Entity\BlockInteractor;

/**
 * BlockInteractorTest class.
 *
 * This is the BlockInteractor test class
 *
 * @author Vincent Composieux <composieux@ekino.com>
 */
class BlockInteractorTest extends TestCase
{
    /**
     * Test createNewContainer() method with some values.
     */
    public function testCreateNewContainer()
    {
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->disableOriginalConstructor()->getMock();

        $blockManager = $this->createMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->any())->method('create')->will($this->returnValue(new Block()));

        $blockInteractor = new BlockInteractor($registry, $blockManager);

        $container = $blockInteractor->createNewContainer([
            'enabled' => true,
            'code' => 'my-code',
        ], function ($container) {
            $container->setSetting('layout', '<div class="custom-layout">{{ CONTENT }}</div>');
        });

        $this->assertInstanceOf('\Sonata\BlockBundle\Model\BlockInterface', $container);

        $settings = $container->getSettings();

        $this->assertTrue($container->getEnabled());

        $this->assertEquals('my-code', $settings['code']);
        $this->assertEquals('<div class="custom-layout">{{ CONTENT }}</div>', $settings['layout']);
    }
}
