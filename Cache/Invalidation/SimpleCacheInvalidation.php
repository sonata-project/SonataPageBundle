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
use Monolog\Logger;

class SimpleCacheInvalidation implements InvalidationInterface
{
    protected $logger;

    public function __construct(Logger $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @param array $caches
     * @param \Sonata\PageBundle\Cache\CacheElement $cacheElement
     * @return void
     */
    public function invalidate(array $caches, CacheElement $cacheElement)
    {
        $keys = $cacheElement->getKeys();
        if (!isset($keys['page_id'])) {
            return false;
        }

        foreach ($caches as $cache) {

            if (!$cache instanceof CacheInterface) {
                throw new \RunTimeException('The object must implements the CacheInterface interface');
            }

            try {
                if ($this->logger) {
                    $this->logger->addInfo(sprintf('[%s] flushing cache keys : %s', __CLASS__, json_encode($keys)));
                }

                $cache->flush(array(
                    'page_id' => $keys['page_id']
                ));
            } catch(\Exception $e) {

                if ($this->logger) {
                    $this->logger->addAlert(sprintf('[%s] %s', __CLASS__, $e->getMessage()));
                } else {
                    throw new \RunTimeException(null, null, $e);
                }
            }
        }

        return true;
    }
}