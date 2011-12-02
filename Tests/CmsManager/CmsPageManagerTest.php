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
use Symfony\Component\DependencyInjection\Container;
use Sonata\PageBundle\Tests\Model\Block;
use Sonata\PageBundle\Tests\Model\Page;

class CmsPageManagerTest extends \PHPUnit_Framework_TestCase
{

    public function getManager()
    {
        $blockManager = $this->getMock('Sonata\\PageBundle\\Model\\BlockManagerInterface');
        $pageManager  = $this->getMock('Sonata\\PageBundle\\Model\\PageManagerInterface');
        $templating   = $this->getMock('Symfony\\Component\\Templating\\EngineInterface');
        $cacheInvalidation = $this->getMock('Sonata\\PageBundle\\Cache\\Invalidation\\InvalidationInterface');
        $router = $this->getMock('Symfony\\Component\\Routing\\RouterInterface');

        return new CmsPageManager(
            $templating,
            $cacheInvalidation,
            $router,
            array('not_found' => array('404'), 'fatal' => array('500')),
            $pageManager,
            $blockManager
        );
    }

    public function testIsDecorable()
    {
        // creating mock objects
        $response = $this->getMock('Symfony\\Component\\HttpFoundation\\Response', array('dummy'), array(), 'ResponseMock');

        $request = $this->getMock('Symfony\\Component\\HttpFoundation\\Request', array('getRequestUri'), array(), 'RequestMock');
        $request->expects($this->any())
            ->method('getRequestUri')
            ->will($this->returnValue('/myurl'));


        $manager = $this->getManager();

        //
        $this->assertFalse($manager->isDecorable($request, HttpKernelInterface::SUB_REQUEST, $response));

        //
        $response->headers = new ParameterBag;
        $response->headers->set('Content-Type', 'foo/test');

        $this->assertFalse($manager->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $response->headers->set('Content-Type', 'text/html');
        $response->setStatusCode(404);
        $this->assertFalse($manager->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $response->setStatusCode(200);

        $request->headers->set('x-requested-with', 'XMLHttpRequest');
        $this->assertFalse($manager->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $request->headers->set('x-requested-with', null);

        $request->headers->set('x-sonata-page-decorable', false);
        $this->assertFalse($manager->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        $request->headers->set('x-sonata-page-decorable', true);

        $request->query->set('_route', 'test');
        $manager->setOption('ignore_routes', array('test'));

        $this->assertFalse($manager->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $request->query->set('_route', 'test2');
        $manager->setOption('ignore_route_patterns', array('/test[0-2]{1}/'));
        $this->assertFalse($manager->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $request->query->set('_route', 'ok');
        $manager->setOption('ignore_uri_patterns', array('/(.*)/'));
        $this->assertFalse($manager->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $manager->setOption('ignore_uri_patterns', array('/ok/'));
        $this->assertTrue($manager->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));
    }


    public function testgetBlockService()
    {

        $manager = $this->getManager();

        $block = $this->getMock('Sonata\PageBundle\Model\BlockInterface');
        $block->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('test'));

        $this->assertFalse($manager->getBlockService($block));

        $service = $this->getMock('Sonata\PageBundle\Block\BlockServiceInterface');
        $service->expects($this->any())
            ->method('setManager')
            ->will($this->returnValue(null));

        $manager->addBlockService('test', $service);

        $this->assertInstanceOf(get_class($service), $manager->getBlockService($block));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testgetBlockServiceException()
    {
        $manager = $this->getManager();
        $manager->setDebug(true);

        $block = $this->getMock('Sonata\PageBundle\Model\BlockInterface');
        $block->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('fakse'));

        $manager->getBlockService($block);
    }

    public function testfindContainer()
    {
        $blockManager = $this->getMock('Sonata\\PageBundle\\Model\\BlockManagerInterface');
        $blockManager = $this->getManager()->getBlockManager();
        $blockManager->expects($this->once())
            ->method('createNewContainer')
            ->will($this->returnCallback(function($options) {
                $block = new Block;
                $block->setSettings($options);
                return $block;
        }));

        $blockManager->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $pageManager  = $this->getMock('Sonata\\PageBundle\\Model\\PageManagerInterface');
        $templating   = $this->getMock('Symfony\\Component\\Templating\\EngineInterface');
        $cacheInvalidation = $this->getMock('Sonata\\PageBundle\\Cache\\Invalidation\\InvalidationInterface');
        $router = $this->getMock('Symfony\\Component\\Routing\\RouterInterface');

        $manager = new CmsPageManager(
            $templating,
            $cacheInvalidation,
            $router,
            array('not_found' => array('404'), 'fatal' => array('500')),
            $pageManager,
            $blockManager
        );

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