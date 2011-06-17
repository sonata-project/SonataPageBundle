<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Cache;

use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

class MemcachedCache implements CacheInterface
{
    protected $servers;

    protected $prefix;

    protected $collection;

    public function __construct($prefix, array $servers)
    {
        $this->prefix  = $prefix;
        $this->servers = $servers;
    }

    public function flushAll()
    {
        return $this->getCollection()->flush();
    }

    public function flush(array $keys = array())
    {
        return $this->getCollection()->delete($this->computeCacheKeys(new CacheElement($keys)));
    }

    public function has(CacheElement $cacheElement)
    {
        return $this->getCollection()->get($this->computeCacheKeys($cacheElement)) !== false;
    }

    /**
     * @return \Memcached
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $this->collection = new \Memcached();

            foreach ($this->servers as $server) {
                $this->collection->addServer($server[0], $server[1], $server[2]);
            }
        }

        return $this->collection;
    }

    /**
     * @param CacheElement $cacheElement
     * @return mixed
     */
    public function set(CacheElement $cacheElement)
    {
        $return = $this->getCollection()->set(
            $this->computeCacheKeys($cacheElement),
            $cacheElement->getValue(),
            time() + $cacheElement->getTtl()
        );

        return $return;
    }

    public function computeCacheKeys(CacheElement $cacheElement)
    {
        $keys = $cacheElement->getKeys();

        ksort($keys);

        return md5($this->prefix.serialize($keys));
    }

    /**
     * @param CacheElement $cacheElement
     * @return mixed
     */
    public function get(CacheElement $cacheElement)
    {
        return $this->getCollection()->get($this->computeCacheKeys($cacheElement));
    }
}