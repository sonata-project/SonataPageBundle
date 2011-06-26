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
    protected $classes = array();

    protected $informations = array();

    public function __construct(array $classes = array())
    {
        foreach ($classes as $class => $identifier) {
            $this->addClass($class, $identifier);
        }
    }

    public function addClass($class, $identifier)
    {
        $this->classes[$class] = $identifier;
        $this->informations[$class] = array();
    }

    public function add($object)
    {
        $class = get_class($object);

        if (!isset($this->classes[$class])) {
            if (method_exists($object, 'getCacheIdentifier')) {
                $this->addClass($class, 'getCacheIdentifier');
            } else if (method_exists($object, 'getId')) {
                $this->addClass($class, 'getId');
            } else {
                return;
            }
        }

        $identifier = call_user_func(array($object, $this->classes[$class]));

        if (!in_array($identifier, $this->informations[$class])) {
            $this->informations[$class][] = $identifier;
        }
    }

    public function reset()
    {
        foreach ($this->classes as $class => $identifier) {
            $this->addClass($class, $identifier);
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