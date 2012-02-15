<?php


/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page;

use Sonata\PageBundle\CmsManager\CmsPageManager;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Sonata\PageBundle\Tests\Model\Block;
use Sonata\PageBundle\Tests\Model\Page;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\CacheBundle\Cache\CacheManagerInterface;
use Sonata\PageBundle\Model\BlockInteractorInterface;

class CmsPageManagerTest extends \PHPUnit_Framework_TestCase
{

    public function getManager($services = array())
    {
        $blocInteractor = isset($services['interactor']) ? $services['interactor'] : $this->getMock('Sonata\PageBundle\Model\BlockInteractorInterface');
        $pageManager  = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        return new CmsPageManager($pageManager, $blocInteractor);
    }

    public function testFindContainer()
    {
        $blockInteractor = $this->getMockBuilder('Sonata\PageBundle\Model\BlockInteractorInterface')->getMock();

        $blockInteractor->expects($this->once())
            ->method('createNewContainer')
            ->will($this->returnCallback(function($options) {
                $block = new Block;

                $block->setSettings($options);

                return $block;
        }));


        $manager = $this->getManager(array(
            'interactor' => $blockInteractor
        ));

        $block = new Block;
        $block->setSettings(array('name' => 'findme'));

        $page = new Page;
        $page->addBlocks($block);

        $container = $manager->findContainer('findme', $page);

        $this->assertEquals(spl_object_hash($block), spl_object_hash($container));

        $container = $manager->findContainer('newcontainer', $page);

        $this->assertEquals('newcontainer', $container->getSetting('name'));
    }
}