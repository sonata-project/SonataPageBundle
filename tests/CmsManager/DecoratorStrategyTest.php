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

namespace Sonata\PageBundle\Tests\Page;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\CmsManager\DecoratorStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DecoratorStrategyTest extends TestCase
{
    public function testIsDecorable(): void
    {
        $response = new Response('dummy');
        $request = Request::create('/myurl');

        $strategy = new DecoratorStrategy([], [], []);

        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::SUB_REQUEST, $response));

        $response->headers = new ResponseHeaderBag();
        $response->headers->set('Content-Type', 'foo/test');

        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        $response->headers->set('Content-Type', 'text/html');
        $response->setStatusCode(404);
        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        $response->setStatusCode(200);

        $request->headers->set('x-requested-with', 'XMLHttpRequest');
        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        $request->headers->set('x-requested-with', null);

        $response->headers->set('x-sonata-page-not-decorable', '1');
        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        $response->headers->remove('x-sonata-page-not-decorable');

        $request->headers->set('x-requested-with', 'XMLHttpRequest');
        $response->headers->set('x-sonata-page-decorable', '1');
        $this->assertTrue($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));
    }

    public function testIgnoreRouteNameMatch(): void
    {
        $strategy = new DecoratorStrategy(['test'], [], []);

        $this->assertFalse($strategy->isRouteNameDecorable('test'));
    }

    public function testIgnoreRouteNamePatternsMatch(): void
    {
        $strategy = new DecoratorStrategy([], ['test[0-2]{1}'], []);

        $this->assertFalse($strategy->isRouteNameDecorable('test2'));
    }

    public function testIgnoreUriPatternsMatch(): void
    {
        $strategy = new DecoratorStrategy([], [], ['(.*)']);

        $this->assertFalse($strategy->isRouteUriDecorable('ok'));
    }

    public function testIgnoreUriPatternsNotMatch(): void
    {
        $strategy = new DecoratorStrategy([], [], ['ok']);

        $this->assertFalse($strategy->isRouteUriDecorable('ok'));
    }
}
