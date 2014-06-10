<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Request;

use Sonata\PageBundle\Request\RequestFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;


/**
 *
 */
class RequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $hasFactory = false;

    public function setup()
    {
        $this->hasFactory = version_compare(Kernel::VERSION, '2.5', '>=');

        if ($this->hasFactory) {
            Request::setFactory(null);
        }
    }

    public function tearDown()
    {
        if ($this->hasFactory) {
            Request::setFactory(null);
        }
    }

    public function testHostAndCreate()
    {
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', RequestFactory::create('host', '/'));
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', Request::create('/'));
    }

    public function testHostAndCreateFromGlobals()
    {
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', RequestFactory::createFromGlobals('host'));
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Request', Request::create('/'));
    }

    public function testHostWithPathAndCreate()
    {
        $this->assertInstanceOf('Sonata\PageBundle\Request\SiteRequest', RequestFactory::create('host_with_path', '/'));

        if ($this->hasFactory) {
            $this->assertInstanceOf('Sonata\PageBundle\Request\SiteRequest', Request::create('/'));
        }
    }

    public function testHostWithPathAndCreateFromGlobals()
    {
        $this->assertInstanceOf('Sonata\PageBundle\Request\SiteRequest', RequestFactory::createFromGlobals('host_with_path'));

        if ($this->hasFactory) {
            $this->assertInstanceOf('Sonata\PageBundle\Request\SiteRequest', Request::create('/'));
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidType()
    {
        RequestFactory::createFromGlobals('boom');
    }
}
