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

use Sonata\BlockBundle\Model\BaseBlock;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Block extends BaseBlock implements PageBlockInterface
{
    /**
     * @var int|string|null
     */
    protected $id;

    protected ?PageInterface $page = null;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getPage(): ?PageInterface
    {
        return $this->page;
    }

    public function setPage(?PageInterface $page = null): void
    {
        $this->page = $page;
    }

    public function addChild(BlockInterface $child): void
    {
        $this->children[] = $child;

        $child->setParent($this);

        if ($child instanceof PageBlockInterface) {
            $child->setPage($this->getPage());
        }
    }
}
