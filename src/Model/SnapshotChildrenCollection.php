<?php

declare(strict_types=1);

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
final class SnapshotChildrenCollection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var TransformerInterface
     */
    private $transformer;

    /**
     * @var PageInterface
     */
    private $page;

    /**
     * @var array
     */
    private $collection;

    public function __construct(TransformerInterface $transformer, PageInterface $page)
    {
        $this->transformer = $transformer;
        $this->page = $page;
    }

    public function offsetUnset($offset): void
    {
        $this->load();
        $this->collection->offsetUnset($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->load();
        $this->collection->offsetSet($offset, $value);
    }

    public function offsetGet($offset)
    {
        $this->load();

        return $this->collection->offsetGet($offset);
    }

    public function offsetExists($offset): bool
    {
        $this->load();

        return $this->collection->offsetExists($offset);
    }

    public function getIterator(): \Iterator
    {
        $this->load();

        return $this->collection->getIterator();
    }

    public function count(): int
    {
        $this->load();

        return $this->collection->count();
    }

    /**
     * load the children collection.
     */
    private function load(): void
    {
        if (null === $this->collection) {
            $this->collection = $this->transformer->getChildren($this->page);
        }
    }
}
