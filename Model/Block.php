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

use Sonata\BlockBundle\Model\BaseBlock;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\BlockBundle\Model\BlockInterface;

/**
 * Block
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Block extends BaseBlock implements PageBlockInterface
{
    /**
     * @var PageInterface
     */
    protected $page;

    /**
     * {@inheritDoc}
     */
    public function addChildren(BlockInterface $child)
    {
        $this->children[] = $child;

        $child->setParent($this);

        if ($child instanceof PageBlockInterface) {
            $child->setPage($this->getPage());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setPage(PageInterface $page = null)
    {
        $this->page = $page;
    }

    /**
     * {@inheritDoc}
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Disables children lazy loading
     */
    public function disableChildrenLazyLoading()
    {
        if (is_object($this->children)) {
            $this->children->setInitialized(true);
        }
    }
}
