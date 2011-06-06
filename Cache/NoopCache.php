<?php


namespace Sonata\PageBundle\Cache;

use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

class NoopCache implements CacheInterface
{
    public function flushAll()
    {
        return true;
    }

    public function flush(CacheElement $cacheElement)
    {
        return true;
    }

    public function has(CacheElement $cacheElement)
    {
        return false;
    }

    public function set(CacheElement $cacheElement)
    {
        return true;
    }
    /**
     * @param array $parameters
     * @return string
     */
    public function get(CacheElement $cacheElement)
    {
        throw new \RunTimeException('The NoopCache::get() cannot called');
    }

    public function createResponse(CacheElement $cacheElement)
    {
        return new Response;
    }
}