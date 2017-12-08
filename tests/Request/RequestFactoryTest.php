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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

class RequestFactoryTest extends TestCase
{
    protected $hasFactory = false;

    public function setup(): void
    {
        $this->hasFactory = version_compare(Kernel::VERSION, '2.5', '>=');

        if ($this->hasFactory) {
            Request::setFactory(null);
        }
    }

    public function tearDown(): void
    {
        if ($this->hasFactory) {
            Request::setFactory(null);
        }
    }

    public function testHostAndCreate(): void
    {
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', RequestFactory::create('host', '/'));
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', Request::create('/'));
    }

    public function testHostAndCreateFromGlobals(): void
    {
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', RequestFactory::createFromGlobals('host'));
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', Request::create('/'));
    }

    public function testHostWithPathAndCreate(): void
    {
        $this->assertInstanceOf('Sonata\PageBundle\Request\SiteRequest', RequestFactory::create('host_with_path', '/'));

        if ($this->hasFactory) {
            $this->assertInstanceOf('Sonata\PageBundle\Request\SiteRequest', Request::create('/'));
        }
    }

    public function testHostWithPathAndCreateFromGlobals(): void
    {
        $this->assertInstanceOf('Sonata\PageBundle\Request\SiteRequest', RequestFactory::createFromGlobals('host_with_path'));

        if ($this->hasFactory) {
            $this->assertInstanceOf('Sonata\PageBundle\Request\SiteRequest', Request::create('/'));
        }
    }

    public function testInvalidType(): void
    {
        $this->expectException(\RuntimeException::class);

        RequestFactory::createFromGlobals('boom');
    }
}
