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
use Psr\Log\LoggerInterface;
use Sonata\BlockBundle\Exception\BlockNotFoundException;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Twig\Extension\PageExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

final class PageExtensionTest extends TestCase
{
    public function testAjaxUrl(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())->method('generate')->willReturn('/foo/bar');

        $block = $this->createMock(PageBlockInterface::class);
        $block
            ->expects(static::once())
            ->method('getPage')
            ->willReturn($this->createMock(PageInterface::class));

        $extension = new PageExtension(
            $this->createMock(CmsManagerSelectorInterface::class),
            $this->createMock(SiteSelectorInterface::class),
            $router,
            $this->createMock(BlockHelper::class),
            $this->getRequestStack(new Request()),
            $this->createMock(LoggerInterface::class)
        );

        static::assertSame('/foo/bar', $extension->ajaxUrl($block));
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

        $extension = new PageExtension(
            $this->createMock(CmsManagerSelectorInterface::class),
            $siteSelector,
            $this->createMock(RouterInterface::class),
            $this->createMock(BlockHelper::class),
            $this->getRequestStack($request),
            $this->createMock(LoggerInterface::class)
        );

        $extension->controller('foo');
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testControllerWithoutSite(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/');

        $extension = new PageExtension(
            $this->createMock(CmsManagerSelectorInterface::class),
            $this->createMock(SiteSelectorInterface::class),
            $this->createMock(RouterInterface::class),
            $this->createMock(BlockHelper::class),
            $this->getRequestStack($request),
            $this->createMock(LoggerInterface::class)
        );

        $extension->controller('bar');
    }

    /**
     * @testdox it's return an empty string when the block does not exist.
     */
    public function testDoesNotThrowBlockNotFoundException(): void
    {
        $blockException = new BlockNotFoundException('block foo does not exist.');

        $blockHelper = $this->createMock(BlockHelper::class);
        $blockHelper
            ->expects(static::once())
            ->method('render')
            ->willThrowException($blockException);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('error')
            ->with('block foo does not exist.', ['previous_exception' => $blockException]);

        $pageExtension = new PageExtension(
            $this->createMock(CmsManagerSelectorInterface::class),
            $this->createMock(SiteSelectorInterface::class),
            $this->createMock(RouterInterface::class),
            $blockHelper,
            $this->getRequestStack(new Request()),
            $logger
        );

        $block = $this->createMock(PageBlockInterface::class);

        static::assertSame('', $pageExtension->renderBlock($block));
    }

    /**
     * @testdox it's skipping blocks that should not be rendered.
     */
    public function testSkipBlocksThatShouldNotBeRendered(): void
    {
        $cmsManagerSelector = $this->createMock(CmsManagerSelectorInterface::class);
        $cmsManagerSelector
            ->expects(static::once())
            ->method('isEditor')
            ->willReturn(false);

        $pageExtension = new PageExtension(
            $cmsManagerSelector,
            $this->createMock(SiteSelectorInterface::class),
            $this->createMock(RouterInterface::class),
            $this->createMock(BlockHelper::class),
            $this->getRequestStack(new Request()),
            $this->createMock(LoggerInterface::class),
            true
        );

        $block = $this->createMock(PageBlockInterface::class);
        $block
            ->expects(static::once())
            ->method('getEnabled')
            ->willReturn(false);

        static::assertSame('', $pageExtension->renderBlock($block));
    }

    public function testRenderingBlock(): void
    {
        $block = $this->createMock(PageBlockInterface::class);

        $blockHelper = $this->createMock(BlockHelper::class);
        $blockHelper
            ->expects(static::once())
            ->method('render')
            ->willReturn('my baz block')
            ->with($block, ['foo' => 'bar']);

        $pageExtension = new PageExtension(
            $this->createMock(CmsManagerSelectorInterface::class),
            $this->createMock(SiteSelectorInterface::class),
            $this->createMock(RouterInterface::class),
            $blockHelper,
            $this->getRequestStack(new Request()),
            $this->createMock(LoggerInterface::class)
        );

        static::assertSame('my baz block', $pageExtension->renderBlock($block, ['foo' => 'bar']));
    }

    private function getRequestStack(Request $request): RequestStack
    {
        $stack = new RequestStack();
        $stack->push($request);

        return $stack;
    }
}
