<?php


/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Cache;

use Sonata\PageBundle\Cache\CacheElement;

class CacheElementTest extends \PHPUnit_Framework_TestCase
{
    public function testCache()
    {
        $cacheKeys = array(
          'block_id' => '1'
        );

        $cache = new CacheElement($cacheKeys, 20);

        $this->assertEquals(20, $cache->getTtl());
        $this->assertEquals($cacheKeys, $cache->getKeys());
        $this->assertFalse($cache->isExpired());

        $cache = new CacheElement($cacheKeys, -1);
        $this->assertTrue($cache->isExpired());

        $cache->addKey('foo', 'bar');
        $cache->setValue('foo');

        $this->assertEquals('foo', $cache->getValue());

        $cacheKeys['foo'] = 'bar';
        $this->assertEquals($cacheKeys, $cache->getKeys());
    }

    public function testContextual()
    {
        $cacheKeys = array(
          'block_id' => '1'
        );

        $cache = new CacheElement($cacheKeys);
        $cache->addContextualKey('foo', 'bar');

        $this->assertEquals(array('foo' => 'bar'), $cache->getContextualKeys());

        $cache->setContextualKeys(array('toto' => 'tata'));

        $this->assertEquals(array('toto' => 'tata'), $cache->getContextualKeys());
    }

}
