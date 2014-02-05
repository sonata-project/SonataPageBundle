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

use Sonata\PageBundle\Cache\BlockEsiCache;

/**
 *
 */
class BlockEsiCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @dataProvider      getExceptionCacheKeys
     */
    public function testExceptions($keys)
    {
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $blockRenderer = $this->getMock('Sonata\BlockBundle\Block\BlockRendererInterface');

        $contextManager = $this->getMock('Sonata\BlockBundle\Block\BlockContextManagerInterface');

        $cache = new BlockEsiCache('My Token', array(), $router, 'ban', $blockRenderer, $contextManager);

        $cache->get($keys, 'data');
    }

    public static function getExceptionCacheKeys()
    {
        return array(
            array(array()),
            array(array('block_id' => 7)),
            array(array('block_id' => 7, 'page_id' => 8)),
            array(array('block_id' => 7, 'manager' => 8)),
            array(array('manager' => 7, 'page_id' => 8)),
            array(array('manager' => 7, 'page_id' => 8)),
            array(array('updated_at' => 'foo')),
        );
    }

    public function testInitCache()
    {
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())->method('generate')->will($this->returnValue('http://sonata-project.org/cache/XXX/page/esi/page/5/4?updated_at=as'));

        $blockRenderer = $this->getMock('Sonata\BlockBundle\Block\BlockRendererInterface');
        $contextManager = $this->getMock('Sonata\BlockBundle\Block\BlockContextManagerInterface');

        $cache = new BlockEsiCache('My Token', array(), $router, 'ban', $blockRenderer, $contextManager);

        $this->assertTrue($cache->flush(array()));
        $this->assertTrue($cache->flushAll());

        $keys = array(
            'block_id'   => 4,
            'page_id'    => 5,
            'updated_at' => 'as',
            'manager'    => 'page'
        );

        $cacheElement = $cache->set($keys, 'data');

        $this->assertInstanceOf('Sonata\Cache\CacheElement', $cacheElement);

        $this->assertTrue($cache->has(array('id' => 7)));

        $cacheElement = $cache->get($keys);

        $this->assertInstanceOf('Sonata\Cache\CacheElement', $cacheElement);

        $this->assertEquals('<esi:include src="http://sonata-project.org/cache/XXX/page/esi/page/5/4?updated_at=as" />', $cacheElement->getData()->getContent());
    }

}
