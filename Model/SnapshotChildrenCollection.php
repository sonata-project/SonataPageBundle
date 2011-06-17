<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Model;

class SnapshotChildrenCollection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    protected $manager;

    protected $page;

    protected $collection;

    public function __construct(SnapshotManagerInterface $manager, PageInterface $page)
    {
        $this->manager = $manager;
        $this->page    = $page;
    }

    private function load()
    {
        $this->collection = $this->manager->getChildren($this->page);
    }

    public function offsetUnset($offset)
    {
        $this->load();

        return $this->collection->offsetUnset($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->load();

        return $this->collection->offsetSet($offset, $value);
    }

    public function offsetGet($offset)
    {
        $this->load();

        return $this->collection->offsetGet($offset);
    }

    public function offsetExists($offset)
    {
        $this->load();

        return $this->collection->offsetExists($offset);
    }

    public function getIterator()
    {
        $this->load();

        return $this->collection->getIterator();
    }


    public function count()
    {
        $this->load();

        return $this->collection->count();
    }
}
