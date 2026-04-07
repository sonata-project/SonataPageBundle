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

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\TestCase;
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

/**
 * NEXT_MAJOR: Remove this test.
 */
#[IgnoreDeprecations]
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
            ->willReturn(static::createStub(PageInterface::class));

        $extension = new PageExtension(
            static::createStub(CmsManagerSelectorInterface::class),
            static::createStub(SiteSelectorInterface::class),
            $router,
            static::createStub(BlockHelper::class),
            $this->getRequestStack(new Request())
        );

        static::assertSame('/foo/bar', $extension->ajaxUrl($block));
    }

    #[DoesNotPerformAssertions]
    public function testController(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $site->method('getRelativePath')->willReturn('/foo/bar');

        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $siteSelector->method('retrieve')->willReturn($site);

        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/');

        $extension = new PageExtension(
            static::createStub(CmsManagerSelectorInterface::class),
            $siteSelector,
            static::createStub(RouterInterface::class),
            static::createStub(BlockHelper::class),
            $this->getRequestStack($request)
        );

        $extension->controller('foo');
    }

    #[DoesNotPerformAssertions]
    public function testControllerWithoutSite(): void
    {
        $request = $this->createMock(Request::class);
        $request->method('getPathInfo')->willReturn('/');

        $extension = new PageExtension(
            static::createStub(CmsManagerSelectorInterface::class),
            static::createStub(SiteSelectorInterface::class),
            static::createStub(RouterInterface::class),
            static::createStub(BlockHelper::class),
            $this->getRequestStack($request)
        );

        $extension->controller('bar');
    }

    private function getRequestStack(Request $request): RequestStack
    {
        $stack = new RequestStack();
        $stack->push($request);

        return $stack;
    }
}
