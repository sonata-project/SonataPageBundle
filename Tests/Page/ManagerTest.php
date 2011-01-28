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

use Sonata\PageBundle\Page\Manager;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\DependencyInjection\Container;



class ManagerTest extends \PHPUnit_Framework_TestCase
{


    public function testIsDecorable()
    {

        // creating mock objects
        $response = $this->getMock('Symfony\\Component\\HttpFoundation\\Response', array('dummy'), array(), 'ResponseMock');

        $request = $this->getMock('Symfony\\Component\\HttpFoundation\\Request', array('getRequestUri'), array(), 'RequestMock');
        $request->expects($this->any())
            ->method('getRequestUri')
            ->will($this->returnValue('/myurl'));

        $container = new Container;
        $container->set('request', $request);

        $repository = $this->getMock('Repository');
        
        $manager = new Manager($container, $repository);

        //
        $this->assertFalse($manager->isDecorable(HttpKernelInterface::SUB_REQUEST, $response));

        //
        $response->headers = new ParameterBag;
        $response->headers->set('Content-Type', 'foo/test');

        $this->assertFalse($manager->isDecorable(HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $response->headers->set('Content-Type', 'text/html');
        $response->setStatusCode(404);
        $this->assertFalse($manager->isDecorable(HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $response->setStatusCode(200);
        $manager->setContainer($container);

        $request->headers->set('x-requested-with', 'XMLHttpRequest');
        $this->assertFalse($manager->isDecorable(HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $request->headers->set('x-requested-with', null);
        $request->query->set('_route', 'test');
        $manager->setOption('ignore_routes', array('test'));

        $this->assertFalse($manager->isDecorable(HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $request->query->set('_route', 'test2');
        $manager->setOption('ignore_route_patterns', array('/test[0-2]{1}/'));
        $this->assertFalse($manager->isDecorable(HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $request->query->set('_route', 'ok');
        $manager->setOption('ignore_uri_patterns', array('/(.*)/'));
        $this->assertFalse($manager->isDecorable(HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $manager->setOption('ignore_uri_patterns', array('/ok/'));
        $this->assertTrue($manager->isDecorable(HttpKernelInterface::MASTER_REQUEST, $response));
    }


    public function testgetBlockService()
    {

        $repository = $this->getMock('Repository');

        $container = new Container;
        $container->set('page.block.test', $this);

        $manager = new Manager($container, $repository);

        $block = $this->getMock('block', array('getType'));
        $block->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('test'));

        $this->assertInstanceOf('Sonata\PageBundle\Tests\Page\ManagerTest', $manager->getBlockService($block));
        
    }

    /**
     * @expectedException Exception
     */
    public function testgetBlockServiceException()
    {

        $repository = $this->getMock('Repository');

        $container = new Container;
        $container->set('page.block.test', $this);

        $manager = new Manager($container, $repository);

        $block = $this->getMock('block', array('getType'));
        $block->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('fakse'));

        $manager->getBlockService($block);
    }

    public function testfindContainer()
    {

        $repository = $this->getMock('Repository', array('createNewContainer', 'save'));
        $repository->expects($this->once())
            ->method('createNewContainer')
            ->will($this->returnCallback(function($options) {
                $block = new Block;
                $block->setSettings($options);
                return $block;
        }));

        $repository->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));


        $container = new Container;

        $manager = new Manager($container, $repository);

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