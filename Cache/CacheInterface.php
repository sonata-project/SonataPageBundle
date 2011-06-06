<?php

namespace Sonata\PageBundle\Cache;

use Sonata\PageBundle\Model\BlockInterface;

interface CacheInterface
{
    function get(CacheElement $cacheElement);

    function has(CacheElement $cacheElement);

    function set(CacheElement $cacheElement);

    function flush(CacheElement $cacheElement);

    function flushAll();
}