<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page;

use Sonata\PageBundle\CmsManager\DecoratorStrategy;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class DecoratorStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testIsDecorable()
    {
        $response = new Response('dummy');
        $request  = Request::create('/myurl');

        $strategy = new DecoratorStrategy(array(), array(), array());

        //
        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::SUB_REQUEST, $response));

        //
        $response->headers = new ParameterBag;
        $response->headers->set('Content-Type', 'foo/test');

        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $response->headers->set('Content-Type', 'text/html');
        $response->setStatusCode(404);
        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $response->setStatusCode(200);

        $request->headers->set('x-requested-with', 'XMLHttpRequest');
        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $request->headers->set('x-requested-with', null);

        $response->headers->set('x-sonata-page-decorable', false);
        $this->assertFalse($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));

        //
        $request->headers->set('x-requested-with', 'XMLHttpRequest');
        $response->headers->set('x-sonata-page-decorable', true);
        $this->assertTrue($strategy->isDecorable($request, HttpKernelInterface::MASTER_REQUEST, $response));
    }

    public function testIgnoreRouteNameMatch()
    {
        $strategy = new DecoratorStrategy(array('test'), array(), array());

        $this->assertFalse($strategy->isRouteNameDecorable('test'));
    }

    public function testIgnoreRouteNamePatternsMatch()
    {
        $strategy = new DecoratorStrategy(array(), array('test[0-2]{1}'), array());

        $this->assertFalse($strategy->isRouteNameDecorable('test2'));
    }

    public function testIgnoreUriPatternsMatch()
    {
        $strategy = new DecoratorStrategy(array(), array(), array('(.*)'));

        $this->assertFalse($strategy->isRouteUriDecorable('ok'));
    }

    public function testIgnoreUriPatternsNotMatch()
    {
        $strategy = new DecoratorStrategy(array(), array(), array('ok'));

        $this->assertFalse($strategy->isRouteUriDecorable('ok'));
    }
}
