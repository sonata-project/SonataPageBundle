<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Generator;

use Sonata\PageBundle\Generator\UrlGenerator;

use Symfony\Component\Routing\RequestContext;

/**
 * URL generator test class
 *
 * @author Rémi Marseille <marseille@ekino.com>
 */
class UrlGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testGenerateWithInvalidContext()
    {
        $generator = new UrlGenerator($this->getMock('Symfony\Component\Routing\RouterInterface'));

        $generator->generate($this->getMock('Sonata\PageBundle\Model\PageInterface'));
    }

    /**
     * Test URL generation with a valid context
     */
    public function testGenerateWithValidContext()
    {
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())->method('getContext')->will($this->returnValue(new RequestContext));

        $generator = new UrlGenerator($router);
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $url = $generator->generate($page);
        $this->assertTrue(is_string($url), 'URL is not a string');

        $url = $generator->generate($page, array('key1' => 'value1', 'key2' => 'value2'));
        $this->assertEquals(0, strpos($url, '?key1=value1&key2=value2'), 'Parameters are missing');

        $url = $generator->generate($page, array(), true);
        $this->assertEquals('http://localhost', $url, 'Invalid URL');

        $url = $generator->generate($page, array('key1' => 'value1', 'key2' => 'value2'), true);
        $this->assertEquals('http://localhost?key1=value1&key2=value2', $url, 'Invalid URL');
    }
}
