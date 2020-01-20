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

class CmsPageRouterTest extends TestCase
{
    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    /**
     * @var SiteSelectorInterface
     */
    protected $siteSelector;

    /**
     * @var RouterInterface
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

    public function testMatchToPageFound()
    {
        $this->expectException(ResourceNotFoundException::class);

        $cms = $this->createMock(CmsManagerInterface::class);
        $this->cmsSelector->expects($this->any())->method('retrieve')->willReturn($cms);

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->any())->method('retrieve')->willReturn($site);

        $this->router->match('/');
    }

    public function testMatchOnlyCmsPage()
    {
        $this->expectException(ResourceNotFoundException::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects($this->any())->method('isCms')->willReturn(false);

        $cms = $this->createMock(CmsManagerInterface::class);
        $cms->expects($this->any())->method('getPageByUrl')->willReturn($page);

        $this->cmsSelector->expects($this->any())->method('retrieve')->willReturn($cms);

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->any())->method('retrieve')->willReturn($site);

        $this->router->match('/');
    }

    public function testMatch()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->any())->method('isCms')->willReturn(true);
        $page->expects($this->any())->method('getEnabled')->willReturn(true);

        $cms = $this->createMock(CmsManagerInterface::class);
        $cms->expects($this->any())->method('getPageByUrl')->willReturn($page);
        $cms->expects($this->once())->method('setCurrentPage');

        $this->cmsSelector->expects($this->any())->method('retrieve')->willReturn($cms);

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->any())->method('retrieve')->willReturn($site);

        $route = $this->router->match('/');

        $this->assertSame('sonata.page.page_service_manager:execute', $route['_controller']);
        $this->assertSame('page_slug', $route['_route']);
    }

    public function testGenerateInvalidPage()
    {
        $this->expectException(RouteNotFoundException::class);

        $this->router->generate('foobar');
    }

    public function testGenerateWithPageSlugInvalidParameterException()
    {
        $this->expectException(\RuntimeException::class);

        $this->router->generate('page_slug', []);
    }

    public function testSupports()
    {
        $this->assertTrue($this->router->supports('page_slug'));
        $this->assertTrue($this->router->supports('_page_alias_homepage'));
        $this->assertFalse($this->router->supports('foobar'));
        $this->assertFalse($this->router->supports(new \stdClass()));
        $this->assertTrue($this->router->supports($this->createMock(PageInterface::class)));
    }

    public function testGenerateWithPageSlugInvalidContext()
    {
        $this->expectException(\RuntimeException::class);

        $this->router->generate('page_slug', ['path' => '/path/to/page']);
    }

    public function testGenerateWithPageSlugValid()
    {
        $this->router->setContext(new RequestContext());

        $url = $this->router->generate('page_slug', ['path' => '/my/path']);
        $this->assertSame('/my/path', $url);

        $url = $this->router->generate('page_slug', ['path' => '/my/path', 'foo' => 'bar']);
        $this->assertSame('/my/path?foo=bar', $url);

        $url = $this->router->generate(
            'page_slug',
            ['path' => '/my/path', 'foo' => 'bar'],
            UrlGeneratorInterface::ABSOLUTE_PATH
        );
        $this->assertSame('/my/path?foo=bar', $url);

        $url = $this->router->generate(
            'page_slug',
            ['path' => '/my/path', 'foo' => 'bar'],
            UrlGeneratorInterface::RELATIVE_PATH
        );
        $this->assertSame('my/path?foo=bar', $url);

        $url = $this->router->generate(
            'page_slug',
            ['path' => '/my/path', 'foo' => 'bar'],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $this->assertSame('http://localhost/my/path?foo=bar', $url);

        $url = $this->router->generate(
            'page_slug',
            ['path' => '/my/path', 'foo' => 'bar'],
            UrlGeneratorInterface::NETWORK_PATH
        );
        $this->assertSame('//localhost/my/path?foo=bar', $url);
    }

    /**
     * @group legacy
     */
    public function testGenerateWithPage()
    {
        $page = $this->createMock(PageInterface::class);

        $page->expects($this->exactly(5))->method('isHybrid')->willReturn(false);
        $page->expects($this->exactly(5))->method('getUrl')->willReturn('/test/path');

        $this->router->setContext(new RequestContext());

        $url = $this->router->generate($page, ['key' => 'value']);
        $this->assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertSame('test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertSame('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        $this->assertSame('//localhost/test/path?key=value', $url);
    }

    /**
     * @group legacy
     */
    public function testGenerateWithPageCustomUrl()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(5))->method('isHybrid')->willReturn(false);
        $page->expects($this->exactly(5))->method('getCustomUrl')->willReturn('/test/path');

        $this->router->setContext(new RequestContext());

        $url = $this->router->generate($page, ['key' => 'value']);
        $this->assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertSame('test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertSame('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        $this->assertSame('//localhost/test/path?key=value', $url);
    }

    /**
     * @group legacy
     */
    public function testGenerateWithHybridPage()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(5))->method('isHybrid')->willReturn(true);
        $page->expects($this->exactly(5))->method('getRouteName')->willReturn('test_route');

        $this->defaultRouter->expects($this->at(0))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH)
            )
            ->willReturn('/test/path?key=value');

        $this->defaultRouter->expects($this->at(1))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH)
            )
            ->willReturn('/test/path?key=value');

        $this->defaultRouter->expects($this->at(2))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::RELATIVE_PATH)
            )
            ->willReturn('test/path?key=value');

        $this->defaultRouter->expects($this->at(3))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_URL)
            )
            ->willReturn('http://localhost/test/path?key=value');

        $this->defaultRouter->expects($this->at(4))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::NETWORK_PATH)
            )
            ->willReturn('//localhost/test/path?key=value');

        $url = $this->router->generate($page, ['key' => 'value']);
        $this->assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertSame('/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertSame('test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertSame('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate($page, ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        $this->assertSame('//localhost/test/path?key=value', $url);
    }

    /**
     * @group legacy
     */
    public function testGenerateWithPageAlias()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(5))->method('isHybrid')->willReturn(false);
        $page->expects($this->exactly(5))->method('getUrl')->willReturn('/test/path');

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->any())->method('retrieve')->willReturn($site);

        $cmsManager = $this->createMock(CmsManagerInterface::class);
        $cmsManager->expects($this->exactly(5))->method('getPageByPageAlias')->willReturn($page);
        $this->cmsSelector->expects($this->exactly(5))->method('retrieve')->willReturn($cmsManager);

        $this->router->setContext(new RequestContext());

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value']);
        $this->assertSame('/test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertSame('/test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertSame('test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertSame('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        $this->assertSame('//localhost/test/path?key=value', $url);
    }

    public function testGenerateWithPageAliasFromHybridPage()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(5))->method('isHybrid')->willReturn(true);
        $page->expects($this->exactly(5))->method('getRouteName')->willReturn('test_route');

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->exactly(5))->method('retrieve')->willReturn($site);

        $cmsManager = $this->createMock(CmsManagerInterface::class);
        $cmsManager->expects($this->exactly(5))->method('getPageByPageAlias')->willReturn($page);
        $this->cmsSelector->expects($this->exactly(5))->method('retrieve')->willReturn($cmsManager);

        $this->defaultRouter->expects($this->at(0))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH)
            )
            ->willReturn('/test/key/value');

        $this->defaultRouter->expects($this->at(1))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH)
            )
            ->willReturn('/test/key/value');

        $this->defaultRouter->expects($this->at(2))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::RELATIVE_PATH)
            )
            ->willReturn('test/key/value');

        $this->defaultRouter->expects($this->at(3))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_URL)
            )
            ->willReturn('http://localhost/test/key/value');

        $this->defaultRouter->expects($this->at(4))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::NETWORK_PATH)
            )
            ->willReturn('//localhost/test/key/value');

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value']);
        $this->assertSame('/test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertSame('/test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertSame('test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertSame('http://localhost/test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', ['key' => 'value'], UrlGeneratorInterface::NETWORK_PATH);
        $this->assertSame('//localhost/test/key/value', $url);
    }

    public function testGenerateWithPageAndNewSiteContext()
    {
        $site1 = $this->createMock(SiteInterface::class);
        $site1->expects($this->exactly(1))->method('getRelativePath')->willReturn('/site1');
        $site2 = $this->createMock(SiteInterface::class);
        $site2->expects($this->exactly(1))->method('getRelativePath')->willReturn('/site2');

        $page = $this->createMock(PageInterface::class);

        $page->expects($this->exactly(2))->method('isHybrid')->willReturn(false);
        $page->expects($this->exactly(2))->method('getUrl')->willReturn('/test/path');

        $page2 = clone $page;
        $page2->expects($this->exactly(1))->method('getSite')->willReturn($site2);
        $page->expects($this->exactly(1))->method('getSite')->willReturn($site1);

        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $siteSelector->expects($this->exactly(1))->method('retrieve')->willReturn($site1);

        $this->router->setContext(new SiteRequestContext($siteSelector));

        $url = $this->router->generate($page, ['key' => 'value']);
        $this->assertSame('/site1/test/path?key=value', $url);

        $url = $this->router->generate($page2, ['key' => 'value']);
        $this->assertSame('/site2/test/path?key=value', $url);
    }
}
