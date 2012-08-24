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

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Model\BaseBlock;

use Sonata\PageBundle\Model\PageInterface;

/**
 * Block
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class Block extends BaseBlock
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
        $child->setPage($this->getPage());
    }

    /**
     * Sets a Page
     *
     * @param \Sonata\PageBundle\Model\PageInterface $page
     */
    public function setPage(PageInterface $page = null)
    {
        $this->page = $page;
    }

    /**
     * Return a Page
     *
     * @return \Sonata\PageBundle\Model\PageInterface $page
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