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
use Twig\Environment;

class PageExtensionTest extends TestCase
{
    public function testAjaxUrl(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->once())->method('generate')->will($this->returnValue('/foo/bar'));

        $block = $this->createMock(PageBlockInterface::class);
        $block
            ->expects($this->exactly(2))
            ->method('getPage')
            ->will($this->returnValue($this->createMock(PageInterface::class)));

        $extension = new PageExtension(
            $this->createMock(CmsManagerSelectorInterface::class),
            $this->createMock(SiteSelectorInterface::class),
            $router,
            $this->createMock(BlockHelper::class),
            $this->createMock(HttpKernelExtension::class)
        );

        $this->assertSame('/foo/bar', $extension->ajaxUrl($block));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testController(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $site->method('getRelativePath')->willReturn('/foo/bar');

        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $siteSelector->method('retrieve')->willReturn($site);

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/');

        $globals = $this->createMock(GlobalVariables::class);
        $globals->method('getRequest')->willReturn($request);
        $twigEnvironment = $this->createMock(Environment::class);
        $twigEnvironment->method('getGlobals')->willReturn(['app' => $globals]);

        $extension = new PageExtension(
            $this->createMock(CmsManagerSelectorInterface::class),
            $siteSelector,
            $this->createMock(RouterInterface::class),
            $this->createMock(BlockHelper::class),
            $httpKernelExtension = $this->createMock(HttpKernelExtension::class)
        );
        $extension->initRuntime($twigEnvironment);

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
    public function testControllerWithoutSite(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/');

        $globals = $this->createMock(GlobalVariables::class);
        $globals->method('getRequest')->willReturn($request);

        $twigEnvironment = $this->createMock(Environment::class);
        $twigEnvironment->method('getGlobals')->willReturn(['app' => $globals]);

        $extension = new PageExtension(
            $this->createMock(CmsManagerSelectorInterface::class),
            $this->createMock(SiteSelectorInterface::class),
            $this->createMock(RouterInterface::class),
            $this->createMock(BlockHelper::class),
            $httpKernelExtension = $this->createMock(HttpKernelExtension::class)
        );
        $extension->initRuntime($twigEnvironment);

        if (!method_exists(AppVariable::class, 'getToken')) {
            $httpKernelExtension->expects($this->once())->method('controller')->with('bar', [], []);
        }

        $extension->controller('bar');
    }
}
