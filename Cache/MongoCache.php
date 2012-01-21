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

class MongoCache implements CacheInterface
{
    protected $settings;

    protected $collection;

    /**
     * @param array $servers
     * @param $database
     * @param $collection
     */
    public function __construct(array $servers, $database, $collection)
    {
        $this->settings = array(
            'servers'     => $servers,
            'database'    => $database,
            'collection'  => $collection
        );
    }

    /**
     * @return mixed
     */
    public function flushAll()
    {
        return $this->getCollection()->remove(array());
    }

    /**
     * @param array $keys
     * @return mixed
     */
    public function flush(array $keys = array())
    {
        return $this->getCollection()->remove($keys);
    }

    /**
     * @param CacheElement $cacheElement
     * @return bool
     */
    public function has(CacheElement $cacheElement)
    {
        $keys = $cacheElement->getKeys();
        $keys['_timeout'] = array('$gt' => time());

        return $this->getCollection()->count($keys) > 0;
    }

    /**
     * @return \MongoCollection
     */
    public function getCollection()
    {
        if (!$this->collection) {
            $mongo = new \Mongo(sprintf('mongodb://%s', implode(',', $this->settings['servers'])));

            $this->collection = $mongo
                ->selectDB($this->settings['database'])
                ->selectCollection($this->settings['collection']);
        }

        return $this->collection;
    }

    /**
     * @param CacheElement $cacheElement
     * @return mixed
     */
    public function set(CacheElement $cacheElement)
    {
        $time = time();

        $keys = $cacheElement->getContextualKeys() + $cacheElement->getKeys();
        $keys['_value']       = new \MongoBinData(serialize($cacheElement->getValue()));
        $keys['_updated_at']  = $time;
        $keys['_timeout']     = $time + $cacheElement->getTtl();

        $return = $this->getCollection()->save($keys);

        return $return;
    }

    /**
     * @param CacheElement $cacheElement
     * @return mixed
     */
    public function get(CacheElement $cacheElement)
    {
        $record = $this->getRecord($cacheElement);

        return $record ? unserialize($record['_value']->bin) : null;
    }

    /**
     * @param CacheElement $cacheElement
     * @return array|null
     */
    public function getRecord(CacheElement $cacheElement)
    {
        $keys = $cacheElement->getKeys();
        $keys['_timeout'] = array('$gt' => time());

        $results =  $this->getCollection()->find($keys);

        if ($results->hasNext()) {
            return $results->getNext();
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isContextual()
    {
        return true;
    }
}