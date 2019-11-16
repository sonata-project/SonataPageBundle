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
    public function setup(): void
    {
        Request::setFactory(null);
    }

    public function tearDown(): void
    {
        Request::setFactory(null);
    }

    public function testHostAndCreate(): void
    {
        $this->assertInstanceOf(Request::class, RequestFactory::create('host', '/'));
        $this->assertInstanceOf(Request::class, Request::create('/'));
    }

    public function testHostAndCreateFromGlobals(): void
    {
        $this->assertInstanceOf(Request::class, RequestFactory::createFromGlobals('host'));
        $this->assertInstanceOf(Request::class, Request::create('/'));
    }

    public function testHostWithPathAndCreate(): void
    {
        $this->assertInstanceOf(SiteRequest::class, RequestFactory::create('host_with_path', '/'));
        $this->assertInstanceOf(SiteRequest::class, Request::create('/'));
    }

    public function testHostWithPathAndCreateFromGlobals(): void
    {
        $this->assertInstanceOf(SiteRequest::class, RequestFactory::createFromGlobals('host_with_path'));
        $this->assertInstanceOf(SiteRequest::class, Request::create('/'));
    }

    public function testInvalidType(): void
    {
        $this->expectException(\RuntimeException::class);

        RequestFactory::createFromGlobals('boom');
    }
}
