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

/**
 * BlockInteractorInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface BlockInteractorInterface
{
    /**
     * return a block with the given id
     *
     * @param mixed $id
     *
     * @return \Sonata\BlockBundle\Model\BlockInterface
     */
    public function getBlock($id);

    /**
     * return a flat list if page's blocks
     *
     * @param \Sonata\PageBundle\Model\PageInterface $page
     *
     * @return array
     */
    public function getBlocksById(PageInterface $page);

    /**
     * load blocks attached the given page
     *
     * @param PageInterface $page
     *
     * @return array $blocks
     */
    public function loadPageBlocks(PageInterface $page);

    /**
     * save the block
     *
     * @param array $data
     *
     * @return bool
     */
    public function saveBlocksPosition(array $data = array());

    /**
     * @param array $values
     *
     * @return \Sonata\BlockBundle\Model\BlockInterface
     */
    public function createNewContainer(array $values = array());
}
