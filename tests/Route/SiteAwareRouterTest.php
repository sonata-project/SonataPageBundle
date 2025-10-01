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
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Route\SiteAwareRouter;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class SiteAwareRouterTest extends TestCase
{
    /**
     * @var MockObject&RouterInterface
     */
    private RouterInterface $innerRouter;

    /**
     * @var MockObject&SiteSelectorInterface
     */
    private SiteSelectorInterface $siteSelector;

    /**
     * @var MockObject&SiteManagerInterface
     */
    private SiteManagerInterface $siteManager;

    private SiteAwareRouter $router;

    protected function setUp(): void
    {
        $this->innerRouter = $this->createMock(RouterInterface::class);
        $this->siteSelector = $this->createMock(SiteSelectorInterface::class);
        $this->siteManager = $this->createMock(SiteManagerInterface::class);

        // Setup default route collection
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));
        $collection->add('app_home.fi', new Route('/home'));
        $collection->add('app_about', new Route('/about'));

        $this->innerRouter->method('getRouteCollection')->willReturn($collection);
        $this->innerRouter->method('getContext')->willReturn(new RequestContext());

        // Setup sites with locales
        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');
        $enSite->method('isEnabled')->willReturn(true);

        $fiSite = $this->createMock(SiteInterface::class);
        $fiSite->method('getLocale')->willReturn('fi');
        $fiSite->method('isEnabled')->willReturn(true);

        $this->siteManager->method('findBy')->willReturn([$enSite, $fiSite]);

        $this->router = new SiteAwareRouter(
            $this->innerRouter,
            $this->siteSelector,
            $this->siteManager,
            true
        );
    }

    public function testMatchWithEnglishSite(): void
    {
        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');

        $this->siteSelector->method('retrieve')->willReturn($enSite);

        $matched = $this->router->match('/en/home');

        static::assertArrayHasKey('_route', $matched);
    }

    public function testMatchWithWrongLocaleThrows404(): void
    {
        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');

        $this->siteSelector->method('retrieve')->willReturn($enSite);

        $this->expectException(ResourceNotFoundException::class);

        // Trying to access Finnish route from English site should 404
        $this->router->match('/home');
    }

    public function testMatchFallsBackToInnerRouterWhenNoSiteLocale(): void
    {
        $site = $this->createMock(SiteInterface::class);
        $site->method('getLocale')->willReturn(null);

        $this->siteSelector->method('retrieve')->willReturn($site);

        $this->innerRouter
            ->expects(static::once())
            ->method('match')
            ->with('/test')
            ->willReturn(['_route' => 'test']);

        $result = $this->router->match('/test');

        static::assertSame('test', $result['_route']);
    }

    public function testGenerateWithExplicitLocaleSuffix(): void
    {
        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');

        $this->siteSelector->method('retrieve')->willReturn($enSite);

        $url = $this->router->generate('app_home.en');

        static::assertSame('/en/home', $url);
    }

    public function testGenerateWithBaseNameUsesCurrentSiteLocale(): void
    {
        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');

        $this->siteSelector->method('retrieve')->willReturn($enSite);

        // Should automatically resolve to app_home.en
        $url = $this->router->generate('app_home');

        static::assertSame('/en/home', $url);
    }

    public function testGenerateWithForcedLocaleParameter(): void
    {
        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');

        $this->siteSelector->method('retrieve')->willReturn($enSite);

        // Force Finnish locale even though we're on English site
        $url = $this->router->generate('app_home', ['_locale' => 'fi']);

        static::assertSame('/home', $url);
    }

    public function testGenerateWithForcedLocaleThrowsIfNoVariant(): void
    {
        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');

        $this->siteSelector->method('retrieve')->willReturn($enSite);

        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage("Cannot force locale 'sv'");

        $this->router->generate('app_home', ['_locale' => 'sv']);
    }

    public function testGenerateNeutralRoute(): void
    {
        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');

        $this->siteSelector->method('retrieve')->willReturn($enSite);

        // Mock inner router to handle neutral routes
        $this->innerRouter
            ->method('generate')
            ->with('app_about', [], RouterInterface::ABSOLUTE_PATH)
            ->willReturn('/about');

        $url = $this->router->generate('app_about');

        static::assertSame('/about', $url);
    }

    public function testGetRouteCollection(): void
    {
        $collection = $this->router->getRouteCollection();

        static::assertInstanceOf(RouteCollection::class, $collection);
        static::assertNotNull($collection->get('app_home.en'));
        static::assertNotNull($collection->get('app_home.fi'));
        static::assertNotNull($collection->get('app_about'));
    }

    public function testSetAndGetContext(): void
    {
        $context = new RequestContext('/app', 'POST', 'example.com');

        $this->router->setContext($context);

        static::assertSame('/app', $this->router->getContext()->getBaseUrl());
        static::assertSame('POST', $this->router->getContext()->getMethod());
        static::assertSame('example.com', $this->router->getContext()->getHost());
    }

    public function testAllowedLocalesRestrictsPartitioning(): void
    {
        // Create a collection with a non-locale dotted route
        $collection = new RouteCollection();
        $collection->add('app_home.en', new Route('/en/home'));
        $collection->add('admin.dashboard', new Route('/admin/dashboard'));

        $innerRouter = $this->createMock(RouterInterface::class);
        $innerRouter->method('getRouteCollection')->willReturn($collection);
        $innerRouter->method('getContext')->willReturn(new RequestContext());
        $innerRouter
            ->method('generate')
            ->with('admin.dashboard', [], RouterInterface::ABSOLUTE_PATH)
            ->willReturn('/admin/dashboard');

        // Only 'en' is a valid locale
        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');
        $enSite->method('isEnabled')->willReturn(true);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->method('findBy')->willReturn([$enSite]);

        $router = new SiteAwareRouter(
            $innerRouter,
            $this->siteSelector,
            $siteManager,
            true
        );

        $enSiteMock = $this->createMock(SiteInterface::class);
        $enSiteMock->method('getLocale')->willReturn('en');
        $this->siteSelector->method('retrieve')->willReturn($enSiteMock);

        // admin.dashboard should be treated as neutral route, not locale 'dashboard'
        $url = $router->generate('admin.dashboard');
        static::assertSame('/admin/dashboard', $url);
    }

    public function testCachedAllowedLocales(): void
    {
        // siteManager should only be called once (caching)
        $this->siteManager
            ->expects(static::once())
            ->method('findBy')
            ->with(['enabled' => true])
            ->willReturn([]);

        $enSite = $this->createMock(SiteInterface::class);
        $enSite->method('getLocale')->willReturn('en');
        $this->siteSelector->method('retrieve')->willReturn($enSite);

        // First call initializes and queries DB
        $this->router->match('/en/home');

        // Second call should use cached result
        $this->router->generate('app_home.en');
    }

    public function testStrictRequirements(): void
    {
        // SiteAwareRouter delegates to inner router if it implements ConfigurableRequirementsInterface
        // Since our mock doesn't, it should return null
        static::assertNull($this->router->isStrictRequirements());

        // Setting should not throw
        $this->router->setStrictRequirements(true);
        $this->router->setStrictRequirements(false);

        // Still returns null since inner router doesn't implement the interface
        static::assertNull($this->router->isStrictRequirements());
    }
}
