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

use Sonata\PageBundle\Entity\BlockInteractor;
use Symfony\Bundle\DoctrineBundle\Registry;
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\Block;

/**
 *
 */
class BlockInteractorTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateNewContainer()
    {

        $container = $this->getMockBuilder('Sonata\PageBundle\Model\Block')->getMock();
        $registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')->disableOriginalConstructor()->getMock();
        $blockManager = $this->getMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->any())->method('create')->will($this->returnValue($container));

        $blockInteractor = new BlockInteractor($registry, $blockManager);

        $block = $blockInteractor->createNewContainer();

        $this->assertInstanceOf('Sonata\BlockBundle\Model\BlockInterface', $block);
    }
}
