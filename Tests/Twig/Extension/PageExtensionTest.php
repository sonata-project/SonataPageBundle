<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Twig\Extension;

use Sonata\PageBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Sonata\PageBundle\Twig\Extension\PageExtension;

class PageExtensionTest extends PHPUnit_Framework_TestCase
{
    public function testAjaxUrl()
    {
        $cmsManager = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $siteSelector = $this->createMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $router = $this->createMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->will($this->returnValue('/foo/bar'));
        $blockHelper = $this->getMockBuilder('Sonata\BlockBundle\Templating\Helper\BlockHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $httpKernelExtension = $this->getMockBuilder('Symfony\Bridge\Twig\Extension\HttpKernelExtension')
            ->disableOriginalConstructor()
            ->getMock();

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $block = $this->createMock('Sonata\PageBundle\Model\PageBlockInterface');
        $block->expects($this->exactly(2))->method('getPage')->will($this->returnValue($page));

        $extension = new PageExtension($cmsManager, $siteSelector, $router, $blockHelper, $httpKernelExtension);
        $this->assertEquals('/foo/bar', $extension->ajaxUrl($block));
    }

    public function testController()
    {
        $this->skipInPHP55();
        $cmsManager = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');
        $site->method('getRelativePath')->willReturn('/foo/bar');
        $siteSelector = $this->createMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->method('retrieve')->willReturn($site);
        $router = $this->createMock('Symfony\Component\Routing\RouterInterface');
        $blockHelper = $this->getMockBuilder('Sonata\BlockBundle\Templating\Helper\BlockHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $request->method('getPathInfo')->willReturn('/');
        $globals = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables')
            ->disableOriginalConstructor()
            ->getMock();
        $globals->method('getRequest')->willReturn($request);
        $env = $this->createMock('Twig_Environment');
        $env->method('getGlobals')->willReturn(array('app' => $globals));
        $httpKernelExtension = $this->getMockBuilder('Symfony\Bridge\Twig\Extension\HttpKernelExtension')
            ->disableOriginalConstructor()
            ->getMock();
        $extension = new PageExtension($cmsManager, $siteSelector, $router, $blockHelper, $httpKernelExtension);
        $extension->initRuntime($env);
        if (!method_exists('Symfony\Bridge\Twig\AppVariable', 'getToken')) {
            $httpKernelExtension->expects($this->once())->method('controller')->with(
                'foo',
                array('pathInfo' => '/foo/bar/'),
                array()
            );
        }
        $extension->controller('foo');
    }

    public function testControllerWithoutSite()
    {
        $this->skipInPHP55();
        $cmsManager = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $siteSelector = $this->createMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $router = $this->createMock('Symfony\Component\Routing\RouterInterface');
        $blockHelper = $this->getMockBuilder('Sonata\BlockBundle\Templating\Helper\BlockHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->createMock('Symfony\Component\HttpFoundation\Request');
        $request->method('getPathInfo')->willReturn('/');
        $globals = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables')
            ->disableOriginalConstructor()
            ->getMock();
        $globals->method('getRequest')->willReturn($request);
        $env = $this->createMock('Twig_Environment');
        $env->method('getGlobals')->willReturn(array('app' => $globals));
        $httpKernelExtension = $this->getMockBuilder('Symfony\Bridge\Twig\Extension\HttpKernelExtension')
            ->disableOriginalConstructor()
            ->getMock();
        $extension = new PageExtension($cmsManager, $siteSelector, $router, $blockHelper, $httpKernelExtension);
        $extension->initRuntime($env);
        if (!method_exists('Symfony\Bridge\Twig\AppVariable', 'getToken')) {
            $httpKernelExtension->expects($this->once())->method('controller')->with('bar', array(), array());
        }
        $extension->controller('bar');
    }

    private function skipInPHP55()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '>=') && version_compare(PHP_VERSION, '5.6.0', '<=')) {
            $this->markTestSkipped(
                'This test should be skipped in php 5.5 due to an issue with phpunit.'
            );
        }
    }
}
