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

namespace Sonata\PageBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Request\RequestFactory;
use Sonata\PageBundle\Request\SiteRequest;
use Symfony\Component\HttpFoundation\Request;

class RequestFactoryTest extends TestCase
{
    public function setup()
    {
        Request::setFactory(null);
    }

    public function tearDown()
    {
        Request::setFactory(null);
    }

    public function testHostAndCreate()
    {
        $this->assertInstanceOf(Request::class, RequestFactory::create('host', '/'));
        $this->assertInstanceOf(Request::class, Request::create('/'));
    }

    public function testHostAndCreateFromGlobals()
    {
        $this->assertInstanceOf(Request::class, RequestFactory::createFromGlobals('host'));
        $this->assertInstanceOf(Request::class, Request::create('/'));
    }

    public function testHostWithPathAndCreate()
    {
        $this->assertInstanceOf(SiteRequest::class, RequestFactory::create('host_with_path', '/'));
        $this->assertInstanceOf(SiteRequest::class, Request::create('/'));
    }

    public function testHostWithPathAndCreateFromGlobals()
    {
        $this->assertInstanceOf(SiteRequest::class, RequestFactory::createFromGlobals('host_with_path'));
        $this->assertInstanceOf(SiteRequest::class, Request::create('/'));
    }

    public function testInvalidType()
    {
        $this->expectException(\RuntimeException::class);

        RequestFactory::createFromGlobals('boom');
    }
}
