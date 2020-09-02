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

namespace Sonata\PageBundle\Tests\Functional\Routing;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Sonata\PageBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class RoutingTest extends WebTestCase
{
    /**
     * @group legacy
     *
     * @dataProvider getRoutes
     */
    public function testRoutes(string $name, string $path, array $methods): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get('router');

        $route = $router->getRouteCollection()->get($name);

        $this->assertNotNull($route);
        $this->assertSame($path, $route->getPath());
        $this->assertEmpty(array_diff($methods, $route->getMethods()));

        // define {provider} for data set #17
        $path = str_replace('{provider}', 'test', $path);

        $matchingPath = $path;
        $matchingFormat = '';
        if (\strlen($matchingPath) >= 10 && false !== strpos($matchingPath, '.{_format}', -10)) {
            $matchingFormat = '.json';
            $matchingPath = str_replace('.{_format}', $matchingFormat, $path);
        }

        foreach ($methods as $method) {
            $router->getContext()->setMethod($method);

            // Check paths like "/api/user/users.json".
            $match = $router->match($matchingPath);

            $this->assertSame($name, $match['_route']);

            if ($matchingFormat) {
                $this->assertSame(ltrim($matchingFormat, '.'), $match['_format']);
            }

            $matchingPathWithStrippedFormat = str_replace('.{_format}', '', $path);

            // Check paths like "/api/user/users".
            $match = $router->match($matchingPathWithStrippedFormat);

            $this->assertSame($name, $match['_route']);

            if ($matchingFormat) {
                $this->assertSame(ltrim($matchingFormat, '.'), $match['_format']);
            }
        }
    }

    public function getRoutes(): iterable
    {
        // API
        if (class_exists(Operation::class)) {
            yield ['app.swagger_ui', '/api/doc', ['GET']];
            yield ['app.swagger', '/api/doc.json', ['GET']];
        } else {
            yield ['nelmio_api_doc_index', '/api/doc/{view}', ['GET']];
        }

        // API - Block
        yield ['sonata_api_block_get_block', '/api/page/blocks/{id}.{_format}', ['GET']];
        yield ['sonata_api_block_put_block', '/api/page/blocks/{id}.{_format}', ['PUT']];
        yield ['sonata_api_block_delete_block', '/api/page/blocks/{id}.{_format}', ['DELETE']];

        // API - Page
        yield ['sonata_api_page_get_pages', '/api/page/pages.{_format}', ['GET']];
        yield ['sonata_api_page_get_page', '/api/page/pages/{id}.{_format}', ['GET']];
        yield ['sonata_api_page_get_page_blocks', '/api/page/pages/{id}/blocks.{_format}', ['GET']];
        yield ['sonata_api_page_get_page_pages', '/api/page/pages/{id}/pages.{_format}', ['GET']];
        yield ['sonata_api_page_post_page_block', '/api/page/pages/{id}/blocks.{_format}', ['POST']];
        yield ['sonata_api_page_post_page', '/api/page/pages.{_format}', ['POST']];
        yield ['sonata_api_page_put_page', '/api/page/pages/{id}.{_format}', ['PUT']];
        yield ['sonata_api_page_delete_page', '/api/page/pages/{id}.{_format}', ['DELETE']];
        yield ['sonata_api_page_post_page_snapshot', '/api/page/pages/{id}/snapshots.{_format}', ['POST']];
        yield ['sonata_api_page_post_pages_snapshots', '/api/page/pages/snapshots.{_format}', ['POST']];

        // API - Site
        yield ['sonata_api_site_get_sites', '/api/page/sites.{_format}', ['GET']];
        yield ['sonata_api_site_get_site', '/api/page/sites/{id}.{_format}', ['GET']];
        yield ['sonata_api_site_post_site', '/api/page/sites.{_format}', ['POST']];
        yield ['sonata_api_site_put_site', '/api/page/sites/{id}.{_format}', ['PUT']];
        yield ['sonata_api_site_delete_site', '/api/page/sites/{id}.{_format}', ['DELETE']];

        // API - Snapshot
        yield ['sonata_api_snapshot_get_snapshots', '/api/page/snapshots.{_format}', ['GET']];
        yield ['sonata_api_snapshot_get_snapshot', '/api/page/snapshots/{id}.{_format}', ['GET']];
        yield ['sonata_api_snapshot_delete_snapshot', '/api/page/snapshots/{id}.{_format}', ['DELETE']];
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
