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

use Sonata\PageBundle\Cache\CacheInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;

class DoctrineORMListener implements EventSubscriber
{
    protected $caches = array();

    protected $collectionIdentifiers;

    public function __construct(ModelCollectionIdentifiers $collectionIdentifiers)
    {
        $this->collectionIdentifiers = $collectionIdentifiers;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::preRemove,
            Events::preUpdate
        );
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $this->flush($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->flush($args);
    }

    protected function flush(LifecycleEventArgs $args)
    {
        $identifier = $this->collectionIdentifiers->getIdentifier($args->getEntity());

        if ($identifier === false) {
            return;
        }

        $parameters = array(
            get_class($args->getEntity()) => $identifier
        );

        foreach ($this->caches as $cache) {
            $cache->flush($parameters);
        }
    }

    public function addCache(CacheInterface $cache)
    {
        if (!$cache->isContextual()) {
            return;
        }

        $this->caches[] = $cache;
    }
}