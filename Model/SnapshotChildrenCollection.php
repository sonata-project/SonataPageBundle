<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Model;

/**
 * SnapshotChildrenCollection.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SnapshotChildrenCollection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * @var PageInterface
     */
    protected $page;

    /**
     * @var array
     */
    protected $collection;

    /**
     * @param TransformerInterface $transformer
     * @param PageInterface        $page
     */
    public function __construct(TransformerInterface $transformer, PageInterface $page)
    {
        $this->transformer = $transformer;
        $this->page = $page;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        $this->load();

        return $this->collection->offsetUnset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->load();

        return $this->collection->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        $this->load();

        return $this->collection->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        $this->load();

        return $this->collection->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $this->load();

        return $this->collection->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        $this->load();

        return $this->collection->count();
    }

    /**
     * load the children collection.
     */
    private function load()
    {
        if (null == $this->collection) {
            $this->collection = $this->transformer->getChildren($this->page);
        }
    }
}
