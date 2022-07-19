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

namespace Sonata\PageBundle\Tests\Route;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Request\SiteRequestContext;
use Sonata\PageBundle\Route\CmsPageRouter;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouterInterface;

final class CmsPageRouterTest extends TestCase
{
    /**
     * @var MockObject&CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    /**
     * @var MockObject&SiteSelectorInterface
     */
    protected $siteSelector;

    /**
     * @var MockObject&RouterInterface
     */
    protected $defaultRouter;

    /**
     * @var CmsPageRouter
     */
    protected $router;

    /**
     * Setup test object with its dependencies.
     */
    protected function setUp(): void
    {
        $this->cmsSelector = $this->createMock(CmsManagerSelectorInterface::class);
        $this->siteSelector = $this->createMock(SiteSelectorInterface::class);
        $this->defaultRouter = $this->createMock(RouterInterface::class);

        $this->router = new CmsPageRouter($this->cmsSelector, $this->siteSelector, $this->defaultRouter);
    }

    public function testMatchToPageFound(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $cms = $this->createMock(CmsManagerInterface::class);
        $this->cmsSelector->method('retrieve')->willReturn($cms);

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->method('retrieve')->willReturn($site);

        $this->router->match('/');
    }

    public function testMatchOnlyCmsPage(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $page = $this->createMock(PageInterface::class);
        $page->method('isCms')->willReturn(false);

        $cms = $this->createMock(CmsManagerInterface::class);
        $cms->method('getPageByUrl')->willReturn($page);

        $this->cmsSelector->method('retrieve')->willReturn($cms);

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->method('retrieve')->willReturn($site);

        $this->router->match('/');
    }

    public function testMatch(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->method('isCms')->willReturn(true);
        $page->method('getEnabled')->willReturn(true);

        $cms = $this->createMock(CmsManagerInterface::class);
        $cms->method('getPageByUrl')->willReturn($page);
        $cms->expects(static::once())->method('setCurrentPage');

        $this->cmsSelector->method('retrieve')->willReturn($cms);

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->method('retrieve')->willReturn($site);

        $route = $this->router->match('/');

        static::assertSame('sonata.page.page_service_manager:execute', $route['_controller']);
        static::assertSame('page_slug', $route['_route']);
    }

    public function testGenerateInvalidPage(): void
    {
        $this->expectException(RouteNotFoundException::class);

        $this->router->generate('foobar');
    }

    public function testGenerateWithPageSlugInvalidParameterException(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->router->generate('page_slug', []);
    }

    public function testSupports(): void
    {
        static::assertTrue($this->router->supports('page_slug'));
        static::assertTrue($this->router->supports('_page_alias_homepage'));
        static::assertFalse($this->router->supports('foobar'));
        static::assertFalse($this->router->supports(new \stdClass()));
        static::assertTrue($this->router->supports($this->createMock(PageInterface::class)));
    }

    public function testGenerateWithPageSlugInvalidContext(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->router->generate('page_slug', ['path' => '/path/to/page']);
    }

    public function testGenerateWithPageSlugValid(): void
    {
        $this->router->setContext(new RequestContext());

        $url = $this->router->generate('page_slug', ['path' => '/my/path']);
        static::assertSame('/my/path', $url);

        $url = $this->router->generate('page_slug', ['path' => '/my/path', 'foo' => 'bar']);
        static::assertSame('/my/path?foo=bar', $url);

        $url = $this->router->generate(
            'page_slug',
            ['path' => '/my/path', 'foo' => 'bar'],
            UrlGeneratorInterface::ABSOLUTE_PATH
        );
        static::assertSame('/my/path?foo=bar', $url);

        $url = $this->router->generate(
            'page_slug',
            ['path' => '/my/path', 'foo' => 'bar'],
            UrlGeneratorInterface::RELATIVE_PATH
        );
        static::assertSame('my/path?foo=bar', $url);

        $url = $this->router->generate(
            'page_slug',
            ['path' => '/my/path', 'foo' => 'bar'],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        static::assertSame('http://localhost/my/path?foo=bar', $url);

        $url = $this->router->generate(
            'page_slug',
            ['path' => '/my/path', 'foo' => 'bar'],
            UrlGeneratorInterface::NETWORK_PATH
        );
        static::assertSame('//localhost/my/path?foo=bar', $url);
    }

    public function testGenerateWithPage(): void
    {
        $page = $this->createMock(PageInterface::class);
        $site = $this->createMock(SiteInterface::class);

        $page->expects(static::exactly(5))->method('isHybrid')->willReturn(false);
        $page->expects(static::exactly(5))->method('getCustomUrl')->willReturn('/test/path');
        $page->expects(static::exactly(5))->method('getSite')->willReturn($site);
        $site->expects(static::exactly(7))->method('isLocalhost')->willReturn(true);

        $this->siteSelector->method('retrieve')->willReturn($site);
        $this->router->setContext(new SiteRequestContext(
            $this->siteSelector
        ));

        $url = $this->router->generate($page, ['key' => 'value']);
        static::assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        static::assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        static::assertSame('test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        static::assertSame('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        static::assertSame('//localhost/test/path?key=value', $url);
    }

    /**
     * @group legacy
     */
    public function testGenerateWithPageCustomUrl(): void
    {
        $page = $this->createMock(PageInterface::class);
        $site = $this->createMock(SiteInterface::class);

        $page->expects(static::exactly(5))->method('isHybrid')->willReturn(false);
        $page->expects(static::exactly(5))->method('getCustomUrl')->willReturn('/test/path');
        $page->expects(static::exactly(5))->method('getSite')->willReturn($site);
        $site->expects(static::exactly(7))->method('isLocalhost')->willReturn(true);

        $this->siteSelector->method('retrieve')->willReturn($site);
        $this->router->setContext(new SiteRequestContext(
            $this->siteSelector
        ));

        $url = $this->router->generate($page, ['key' => 'value']);
        static::assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        static::assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        static::assertSame('test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        static::assertSame('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        static::assertSame('//localhost/test/path?key=value', $url);
    }

    /**
     * @group legacy
     */
    public function testGenerateWithHybridPage(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::exactly(5))->method('isHybrid')->willReturn(true);
        $page->expects(static::exactly(5))->method('getRouteName')->willReturn('test_route');

        $this->defaultRouter->expects(static::exactly(5))->method('generate')->willReturnMap([
            ['test_route', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH, '/test/path?key=value'],
            ['test_route', ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH, 'test/path?key=value'],
            ['test_route', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://localhost/test/path?key=value'],
            ['test_route', ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH, '//localhost/test/path?key=value'],
        ]);

        $url = $this->router->generate($page, ['key' => 'value']);
        static::assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        static::assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        static::assertSame('test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        static::assertSame('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        static::assertSame('//localhost/test/path?key=value', $url);
    }

    /**
     * @group legacy
     */
    public function testGenerateWithPageAlias(): void
    {
        $page = $this->createMock(PageInterface::class);
        $site = $this->createMock(SiteInterface::class);

        $page->expects(static::exactly(5))->method('isHybrid')->willReturn(false);
        $page->expects(static::exactly(5))->method('getUrl')->willReturn('/test/path');
        $page->expects(static::exactly(5))->method('getSite')->willReturn($site);
        $site->expects(static::exactly(7))->method('isLocalhost')->willReturn(true);

        $cmsManager = $this->createMock(CmsManagerInterface::class);
        $cmsManager->expects(static::exactly(5))->method('getPageByPageAlias')->willReturn($page);
        $this->cmsSelector->expects(static::exactly(5))->method('retrieve')->willReturn($cmsManager);

        $this->siteSelector->method('retrieve')->willReturn($site);
        $this->router->setContext(new SiteRequestContext(
            $this->siteSelector
        ));

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value']);
        static::assertSame('/test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        static::assertSame('/test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        static::assertSame('test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        static::assertSame('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        static::assertSame('//localhost/test/path?key=value', $url);
    }

    public function testGenerateWithPageAliasFromHybridPage(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::exactly(5))->method('isHybrid')->willReturn(true);
        $page->expects(static::exactly(5))->method('getRouteName')->willReturn('test_route');

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects(static::exactly(5))->method('retrieve')->willReturn($site);

        $cmsManager = $this->createMock(CmsManagerInterface::class);
        $cmsManager->expects(static::exactly(5))->method('getPageByPageAlias')->willReturn($page);
        $this->cmsSelector->expects(static::exactly(5))->method('retrieve')->willReturn($cmsManager);

        $this->defaultRouter->expects(static::exactly(5))->method('generate')->willReturnMap([
            ['test_route', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH, '/test/key/value'],
            ['test_route', ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH, 'test/key/value'],
            ['test_route', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL, 'http://localhost/test/key/value'],
            ['test_route', ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH, '//localhost/test/key/value'],
        ]);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value']);
        static::assertSame('/test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        static::assertSame('/test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        static::assertSame('test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        static::assertSame('http://localhost/test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        static::assertSame('//localhost/test/key/value', $url);
    }

    public function testGenerateWithPageAndNewSiteContext(): void
    {
        $site1 = $this->createMock(SiteInterface::class);
        $site1->expects(static::once())->method('getRelativePath')->willReturn('/site1');
        $site2 = $this->createMock(SiteInterface::class);
        $site2->expects(static::once())->method('getRelativePath')->willReturn('/site2');

        $page = $this->createMock(PageInterface::class);

        $page->expects(static::exactly(2))->method('isHybrid')->willReturn(false);
        $page->expects(static::exactly(2))->method('getUrl')->willReturn('/test/path');

        $page2 = clone $page;
        $page2->expects(static::once())->method('getSite')->willReturn($site2);
        $page->expects(static::once())->method('getSite')->willReturn($site1);

        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $siteSelector->expects(static::once())->method('retrieve')->willReturn($site1);

        $this->router->setContext(new SiteRequestContext($siteSelector));

        $url = $this->router->generate($page, ['key' => 'value']);
        static::assertSame('/site1/test/path?key=value', $url);

        $url = $this->router->generate($page2, ['key' => 'value']);
        static::assertSame('/site2/test/path?key=value', $url);
    }
}
