<?php


/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Cache\Invalidation;

use Sonata\PageBundle\Cache\CacheElement;
use Sonata\PageBundle\Cache\Invalidation\SimpleCacheInvalidation;
use Sonata\PageBundle\Cache\CacheInterface;

class SimpleCacheInvalidationTest_Cache
{}

class SimpleCacheInvalidationTest extends \PHPUnit_Framework_TestCase
{
    public function testInvalidate()
    {
        $cacheInvalidation = new SimpleCacheInvalidation;

        $cache = $this->getMock('Sonata\PageBundle\Cache\CacheInterface');
        $cache->expects($this->exactly(1))
            ->method('flush');

        $caches = array($cache);

        $cacheElement = new CacheElement(array('test' => 1));
        $this->assertFalse($cacheInvalidation->invalidate($caches, $cacheElement));

        $cacheElement = new CacheElement(array('page_id' => 1));
        $this->assertTrue($cacheInvalidation->invalidate($caches, $cacheElement));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testWithoutLogger()
    {
        $cacheInvalidation = new SimpleCacheInvalidation;

        $cache = $this->getMock('Sonata\PageBundle\Cache\CacheInterface');
        $cache->expects($this->exactly(1))
            ->method('flush')
            ->will($this->throwException(new \Exception));

        $caches = array($cache);
        $cacheElement = new CacheElement(array('page_id' => 1));

        $cacheInvalidation->invalidate($caches, $cacheElement);
    }

    public function testWithLogger()
    {
        $logger = $this->getMock('Monolog\Logger', array(), array(), '', false);
        $logger->expects($this->exactly(1))
            ->method('addInfo');
        $logger->expects($this->exactly(1))
            ->method('addAlert');

        $cacheInvalidation = new SimpleCacheInvalidation($logger);

        $cache = $this->getMock('Sonata\PageBundle\Cache\CacheInterface');
        $cache->expects($this->exactly(1))
            ->method('flush')
            ->will($this->throwException(new \Exception));

        $caches = array($cache);
        $cacheElement = new CacheElement(array('page_id' => 1));

        $cacheInvalidation->invalidate($caches, $cacheElement);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidCacheHandle()
    {
        $cacheInvalidation = new SimpleCacheInvalidation();

        $caches = array(new SimpleCacheInvalidationTest_Cache);
        $cacheElement = new CacheElement(array('page_id' => 1));

        $cacheInvalidation->invalidate($caches, $cacheElement);

    }
}
