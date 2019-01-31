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
    public function setup(): void
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
        $this->cmsSelector->expects($this->any())->method('retrieve')->will($this->returnValue($cms));

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $this->router->match('/');
    }

    public function testMatchOnlyCmsPage(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects($this->any())->method('isCms')->will($this->returnValue(false));

        $cms = $this->createMock(CmsManagerInterface::class);
        $cms->expects($this->any())->method('getPageByUrl')->will($this->returnValue($page));

        $this->cmsSelector->expects($this->any())->method('retrieve')->will($this->returnValue($cms));

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $this->router->match('/');
    }

    public function testMatch(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->any())->method('isCms')->will($this->returnValue(true));
        $page->expects($this->any())->method('getEnabled')->will($this->returnValue(true));

        $cms = $this->createMock(CmsManagerInterface::class);
        $cms->expects($this->any())->method('getPageByUrl')->will($this->returnValue($page));
        $cms->expects($this->once())->method('setCurrentPage');

        $this->cmsSelector->expects($this->any())->method('retrieve')->will($this->returnValue($cms));

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $route = $this->router->match('/');

        $this->assertSame('sonata.page.page_service_manager:execute', $route['_controller']);
        $this->assertSame('page_slug', $route['_route']);
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
        $this->assertTrue($this->router->supports('page_slug'));
        $this->assertTrue($this->router->supports('_page_alias_homepage'));
        $this->assertFalse($this->router->supports('foobar'));
        $this->assertFalse($this->router->supports(new \stdClass()));
        $this->assertTrue($this->router->supports($this->createMock(PageInterface::class)));
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
    public function testGenerateWithPage(): void
    {
        $page = $this->createMock(PageInterface::class);

        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(false));
        $page->expects($this->exactly(5))->method('getUrl')->will($this->returnValue('/test/path'));

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
    public function testGenerateWithPageCustomUrl(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(false));
        $page->expects($this->exactly(5))->method('getCustomUrl')->will($this->returnValue('/test/path'));

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
    public function testGenerateWithHybridPage(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(true));
        $page->expects($this->exactly(5))->method('getRouteName')->will($this->returnValue('test_route'));

        $this->defaultRouter->expects($this->at(0))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH)
            )
            ->will($this->returnValue('/test/path?key=value'));

        $this->defaultRouter->expects($this->at(1))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH)
            )
            ->will($this->returnValue('/test/path?key=value'));

        $this->defaultRouter->expects($this->at(2))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::RELATIVE_PATH)
            )
            ->will($this->returnValue('test/path?key=value'));

        $this->defaultRouter->expects($this->at(3))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_URL)
            )
            ->will($this->returnValue('http://localhost/test/path?key=value'));

        $this->defaultRouter->expects($this->at(4))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::NETWORK_PATH)
            )
            ->will($this->returnValue('//localhost/test/path?key=value'));

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
    public function testGenerateWithPageAlias(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(false));
        $page->expects($this->exactly(5))->method('getUrl')->will($this->returnValue('/test/path'));

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $cmsManager = $this->createMock(CmsManagerInterface::class);
        $cmsManager->expects($this->exactly(5))->method('getPageByPageAlias')->will($this->returnValue($page));
        $this->cmsSelector->expects($this->exactly(5))->method('retrieve')->will($this->returnValue($cmsManager));

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

    public function testGenerateWithPageAliasFromHybridPage(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(true));
        $page->expects($this->exactly(5))->method('getRouteName')->will($this->returnValue('test_route'));

        $site = $this->createMock(SiteInterface::class);
        $this->siteSelector->expects($this->exactly(5))->method('retrieve')->will($this->returnValue($site));

        $cmsManager = $this->createMock(CmsManagerInterface::class);
        $cmsManager->expects($this->exactly(5))->method('getPageByPageAlias')->will($this->returnValue($page));
        $this->cmsSelector->expects($this->exactly(5))->method('retrieve')->will($this->returnValue($cmsManager));

        $this->defaultRouter->expects($this->at(0))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH)
            )
            ->will($this->returnValue('/test/key/value'));

        $this->defaultRouter->expects($this->at(1))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH)
            )
            ->will($this->returnValue('/test/key/value'));

        $this->defaultRouter->expects($this->at(2))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::RELATIVE_PATH)
            )
            ->will($this->returnValue('test/key/value'));

        $this->defaultRouter->expects($this->at(3))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::ABSOLUTE_URL)
            )
            ->will($this->returnValue('http://localhost/test/key/value'));

        $this->defaultRouter->expects($this->at(4))
            ->method('generate')
            ->with(
                $this->equalTo('test_route'),
                $this->equalTo(['key' => 'value']),
                $this->equalTo(UrlGeneratorInterface::NETWORK_PATH)
            )
            ->will($this->returnValue('//localhost/test/key/value'));

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

    public function testGenerateWithPageAndNewSiteContext(): void
    {
        $site1 = $this->createMock(SiteInterface::class);
        $site1->expects($this->exactly(1))->method('getRelativePath')->will($this->returnValue('/site1'));
        $site2 = $this->createMock(SiteInterface::class);
        $site2->expects($this->exactly(1))->method('getRelativePath')->will($this->returnValue('/site2'));

        $page = $this->createMock(PageInterface::class);

        $page->expects($this->exactly(2))->method('isHybrid')->will($this->returnValue(false));
        $page->expects($this->exactly(2))->method('getUrl')->will($this->returnValue('/test/path'));

        $page2 = clone $page;
        $page2->expects($this->exactly(1))->method('getSite')->will($this->returnValue($site2));
        $page->expects($this->exactly(1))->method('getSite')->will($this->returnValue($site1));

        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $siteSelector->expects($this->exactly(1))->method('retrieve')->will($this->returnValue($site1));

        $this->router->setContext(new SiteRequestContext($siteSelector));

        $url = $this->router->generate($page, ['key' => 'value']);
        $this->assertSame('/site1/test/path?key=value', $url);

        $url = $this->router->generate($page2, ['key' => 'value']);
        $this->assertSame('/site2/test/path?key=value', $url);
    }
}
