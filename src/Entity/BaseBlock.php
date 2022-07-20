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

namespace Sonata\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sonata\PageBundle\Model\Block;
use Sonata\PageBundle\Model\PageBlockInterface;

/**
 * The class stores block information.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseBlock extends Block
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var Collection<array-key, PageBlockInterface>
     */
    protected $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();

        parent::__construct();
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * Updates dates before creating/updating entity.
     */
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Updates dates before updating entity.
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
