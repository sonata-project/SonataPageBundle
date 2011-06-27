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

class Recorder
{
    protected $collectionIdentifiers;

    protected $informations = array();

    public function __construct(ModelCollectionIdentifiers $collectionIdentifiers)
    {
        $this->collectionIdentifiers = $collectionIdentifiers;
    }

    public function add($object)
    {
        $class = get_class($object);

        $identifier = $this->collectionIdentifiers->getIdentifier($object);

        if ($identifier === false) {
            return;
        }

        if (!isset($this->informations[$class])) {
            $this->informations[$class] = array();
        }

        if (!in_array($identifier, $this->informations[$class])) {
            $this->informations[$class][] = $identifier;
        }
    }

    public function reset()
    {
        foreach ($this->informations as $class => $identifier) {
            $this->informations[$class] = array();
        }
    }

    public function get($name = null)
    {
        if ($name) {
            return $this->informations[$name];
        }

        return $this->informations;
    }
}