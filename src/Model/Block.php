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
 * Block.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Block extends BaseBlock implements PageBlockInterface
{
    /**
     * @var PageInterface
     */
    protected $page;

    public function addChildren(BlockInterface $children): void
    {
        $this->children[] = $children;

        $children->setParent($this);

        if ($children instanceof PageBlockInterface) {
            $children->setPage($this->getPage());
        }
    }

    public function setPage(?PageInterface $page = null): void
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }
}
