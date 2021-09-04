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
use Sonata\PageBundle\CmsManager\DecoratorStrategy;
use Sonata\PageBundle\Entity\PageManager;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Route\RoutePageGenerator;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class RoutePageGeneratorTest extends TestCase
{
    /**
     * @var RoutePageGenerator
     */
    protected $routePageGenerator;

    /**
     * Set up dependencies.
     */
    protected function setUp(): void
    {
        $this->routePageGenerator = $this->getRoutePageGenerator();
    }

    /**
     * Tests site update route method with.
     */
    public function testUpdateRoutes(): void
    {
        $site = $this->getSiteMock();

        $tmpFile = tmpfile();

        $this->routePageGenerator->update($site, new StreamOutput($tmpFile));

        fseek($tmpFile, 0);

        $output = '';

        while (!feof($tmpFile)) {
            $output = fread($tmpFile, 4096);
        }

        static::assertMatchesRegularExpression('/CREATE(.*)route1(.*)\/first_custom_route/', $output);
        static::assertMatchesRegularExpression('/CREATE(.*)route1(.*)\/first_custom_route/', $output);
        static::assertMatchesRegularExpression('/CREATE(.*)test_hybrid_page_with_good_host(.*)\/third_custom_route/', $output);
        static::assertMatchesRegularExpression('/CREATE(.*)404/', $output);
        static::assertMatchesRegularExpression('/CREATE(.*)500/', $output);

        static::assertMatchesRegularExpression('/DISABLE(.*)test_hybrid_page_with_bad_host(.*)\/fourth_custom_route/', $output);

        static::assertMatchesRegularExpression('/UPDATE(.*)test_hybrid_page_with_bad_host(.*)\/fourth_custom_route/', $output);

        static::assertMatchesRegularExpression('/ERROR(.*)test_hybrid_page_not_exists/', $output);
    }

    /**
     * Tests site update route method with.
     */
    public function testUpdateRoutesClean(): void
    {
        $site = $this->getSiteMock();

        $tmpFile = tmpfile();

        $this->routePageGenerator->update($site, new StreamOutput($tmpFile), true);

        fseek($tmpFile, 0);

        $output = '';

        while (!feof($tmpFile)) {
            $output = fread($tmpFile, 4096);
        }

        static::assertMatchesRegularExpression('#CREATE(.*)route1(.*)/first_custom_route#', $output);
        static::assertMatchesRegularExpression('#CREATE(.*)route1(.*)/first_custom_route#', $output);
        static::assertMatchesRegularExpression('#CREATE(.*)test_hybrid_page_with_good_host(.*)/third_custom_route#', $output);
        static::assertMatchesRegularExpression('#CREATE(.*)404#', $output);
        static::assertMatchesRegularExpression('#CREATE(.*)500#', $output);

        static::assertMatchesRegularExpression('#DISABLE(.*)test_hybrid_page_with_bad_host(.*)/fourth_custom_route#', $output);

        static::assertMatchesRegularExpression('#UPDATE(.*)test_hybrid_page_with_bad_host(.*)/fourth_custom_route#', $output);

        static::assertMatchesRegularExpression('#REMOVED(.*)test_hybrid_page_not_exists#', $output);
    }

    /**
     * Returns a mock of a site model.
     */
    protected function getSiteMock(): SiteInterface
    {
        $site = $this->createMock(SiteInterface::class);
        $site->method('getHost')->willReturn('sonata-project.org');
        $site->method('getId')->willReturn(1);

        return $site;
    }

    /**
     * Returns a mock of Symfony router.
     */
    protected function getRouterMock(): RouterInterface
    {
        $collection = new RouteCollection();
        $collection->add('route1', new Route('first_custom_route'));
        $collection->add('route2', new Route('second_custom_route'));
        $collection->add('test_hybrid_page_with_good_host', new Route(
            'third_custom_route',
            [],
            ['tld' => 'fr|org'],
            [],
            'sonata-project.{tld}'
        ));
        $collection->add('test_hybrid_page_with_bad_host', new Route(
            'fourth_custom_route',
            [],
            [],
            [],
            'sonata-project.com'
        ));

        $router = $this->createMock(RouterInterface::class);
        $router->method('getRouteCollection')->willReturn($collection);

        return $router;
    }

    /**
     * Returns Sonata route page generator service.
     */
    protected function getRoutePageGenerator(): RoutePageGenerator
    {
        $router = $this->getRouterMock();

        $pageManager = $this->createMock(PageManager::class);
        $pageManager->method('create')->willReturn(new Page());

        $hybridPageNotExists = new Page();
        $hybridPageNotExists->setRouteName('test_hybrid_page_not_exists');

        $hybridPageWithGoodHost = new Page();
        $hybridPageWithGoodHost->setRouteName('test_hybrid_page_with_good_host');

        $hybridPageWithBadHost = new Page();
        $hybridPageWithBadHost->setRouteName('test_hybrid_page_with_bad_host');

        $pageManager->expects(static::atLeastOnce())
            ->method('findOneBy')
            ->willReturnMap([
                [['routeName' => 'test_hybrid_page_with_bad_host', 'site' => 1], null, $hybridPageWithBadHost],
            ]);

        $pageManager
            ->method('getHybridPages')
            ->willReturn([$hybridPageNotExists, $hybridPageWithGoodHost, $hybridPageWithBadHost]);

        $decoratorStrategy = new DecoratorStrategy([], [], []);

        $exceptionListener = $this->createMock(ExceptionListener::class);
        $exceptionListener->method('getHttpErrorCodes')->willReturn([404, 500]);

        return new RoutePageGenerator($router, $pageManager, $decoratorStrategy, $exceptionListener);
    }
}
