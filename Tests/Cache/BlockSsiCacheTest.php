<?php

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
use Sonata\PageBundle\Cache\BlockSsiCache;

class BlockSsiCacheTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @dataProvider      getExceptionCacheKeys
     */
    public function testExceptions($keys)
    {
        $router = $this->createMock('Symfony\Component\Routing\RouterInterface');

        $blockRenderer = $this->createMock('Sonata\BlockBundle\Block\BlockRendererInterface');
        $contextManager = $this->createMock('Sonata\BlockBundle\Block\BlockContextManagerInterface');

        $cache = new BlockSsiCache('', $router, $blockRenderer, $contextManager);

        $cache->get($keys, 'data');
    }

    public static function getExceptionCacheKeys()
    {
        return [
            [[]],
            [['block_id' => 7]],
            [['block_id' => 7, 'page_id' => 8]],
            [['block_id' => 7, 'manager' => 8]],
            [['manager' => 7, 'page_id' => 8]],
            [['manager' => 7, 'page_id' => 8]],
            [['updated_at' => 'foo']],
        ];
    }

    public function testInitCache()
    {
        $router = $this->createMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->any())->method('generate')->will($this->returnValue('/cache/page/esi/XXXXX/page/5/4?updated_at=as'));

        $blockRenderer = $this->createMock('Sonata\BlockBundle\Block\BlockRendererInterface');
        $contextManager = $this->createMock('Sonata\BlockBundle\Block\BlockContextManagerInterface');

        $cache = new BlockSsiCache('', $router, $blockRenderer, $contextManager);

        $this->assertTrue($cache->flush([]));
        $this->assertTrue($cache->flushAll());

        $keys = [
            'block_id' => 4,
            'page_id' => 5,
            'updated_at' => 'as',
            'manager' => 'page',
        ];

        $cacheElement = $cache->set($keys, 'data');

        $this->assertInstanceOf('Sonata\Cache\CacheElement', $cacheElement);

        $this->assertTrue($cache->has(['id' => 7]));

        $cacheElement = $cache->get($keys);

        $this->assertInstanceOf('Sonata\Cache\CacheElement', $cacheElement);

        $this->assertEquals('<!--# include virtual="/cache/page/esi/XXXXX/page/5/4?updated_at=as" -->', $cacheElement->getData()->getContent());
    }
}
