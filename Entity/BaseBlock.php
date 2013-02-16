<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Entity;

use Sonata\PageBundle\Model\Block;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * The class stores block information
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseBlock extends Block
{
    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        $this->children = new ArrayCollection;

        parent::__construct();
    }

    /**
     * Updates dates before creating/updating entity
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime;
        $this->updatedAt = new \DateTime;
    }

    /**
     * Updates dates before updating entity
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime;
    }

    /**
     * {@inheritDoc}
     */
    public function setChildren($children)
    {
        $this->children = new ArrayCollection;

        foreach ($children as $child) {
            $this->addChildren($child);
        }
    }
}
