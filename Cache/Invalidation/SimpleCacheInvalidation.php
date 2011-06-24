<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Cache\Invalidation;

use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\PageBundle\Cache\CacheInterface;

class SimpleCacheInvalidation implements InvalidationInterface
{

    /**
     * @param array $caches
     * @param \Sonata\PageBundle\Cache\CacheElement $cacheElement
     * @return void
     */
    public function invalidate(array $caches, CacheElement $cacheElement)
    {
        $keys = $cacheElement->getKeys();

        foreach ($caches as $cache) {
            $cache->flush(array(
                'page_id' => $keys['page_id']
            ));
        }
    }
}