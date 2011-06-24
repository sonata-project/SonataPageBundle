<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Util;

class RecursiveBlockIterator extends \ArrayIterator implements \RecursiveIterator
{
    public function __construct($array)
    {
        if (is_object($array)) {
            $array = $array->toArray();
        }

        parent::__construct($array);
    }

    public function getChildren()
    {
        return new self($this->current()->getChildren());
    }

    public function hasChildren()
    {
        return $this->current()->hasChildren();
    }
}