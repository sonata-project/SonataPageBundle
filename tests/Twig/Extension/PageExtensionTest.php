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

namespace Sonata\PageBundle\Tests\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Twig\Extension\PageExtension;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class PageExtensionTest extends TestCase
{
    public function testAjaxUrl()
    {
        $cmsManager = $this->createMock(CmsManagerSelectorInterface::class);
        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())->method('generate')->will($this->returnValue('/foo/bar'));
        $blockHelper = $this->getMockBuilder(BlockHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpKernelExtension = $this->getMockBuilder(HttpKernelExtension::class)
            ->disableOriginalConstructor()
            ->getMock();

        $page = $this->createMock(PageInterface::class);
        $block = $this->createMock(PageBlockInterface::class);
        $block->expects($this->exactly(2))->method('getPage')->will($this->returnValue($page));

        $extension = new PageExtension($cmsManager, $siteSelector, $router, $blockHelper, $httpKernelExtension);
        $this->assertEquals('/foo/bar', $extension->ajaxUrl($block));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testController()
    {
        $cmsManager = $this->createMock(CmsManagerSelectorInterface::class);
        $site = $this->createMock(SiteInterface::class);
        $site->method('getRelativePath')->willReturn('/foo/bar');
        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $siteSelector->method('retrieve')->willReturn($site);
        $router = $this->createMock(RouterInterface::class);
        $blockHelper = $this->getMockBuilder(BlockHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/');
        $globals = $this->getMockBuilder(GlobalVariables::class)
            ->disableOriginalConstructor()
            ->getMock();
        $globals->method('getRequest')->willReturn($request);
        $env = $this->createMock('Twig_Environment');
        $env->method('getGlobals')->willReturn(['app' => $globals]);
        $httpKernelExtension = $this->getMockBuilder(HttpKernelExtension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extension = new PageExtension($cmsManager, $siteSelector, $router, $blockHelper, $httpKernelExtension);
        $extension->initRuntime($env);
        if (!method_exists(AppVariable::class, 'getToken')) {
            $httpKernelExtension->expects($this->once())->method('controller')->with(
                'foo',
                ['pathInfo' => '/foo/bar/'],
                []
            );
        }
        $extension->controller('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testControllerWithoutSite()
    {
        $cmsManager = $this->createMock(CmsManagerSelectorInterface::class);
        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $router = $this->createMock(RouterInterface::class);
        $blockHelper = $this->getMockBuilder(BlockHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/');
        $globals = $this->getMockBuilder(GlobalVariables::class)
            ->disableOriginalConstructor()
            ->getMock();
        $globals->method('getRequest')->willReturn($request);
        $env = $this->createMock('Twig_Environment');
        $env->method('getGlobals')->willReturn(['app' => $globals]);
        $httpKernelExtension = $this->getMockBuilder(HttpKernelExtension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extension = new PageExtension($cmsManager, $siteSelector, $router, $blockHelper, $httpKernelExtension);
        $extension->initRuntime($env);
        if (!method_exists(AppVariable::class, 'getToken')) {
            $httpKernelExtension->expects($this->once())->method('controller')->with('bar', [], []);
        }
        $extension->controller('bar');
    }
}
