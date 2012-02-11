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

interface BlockInteractorInterface
{
    /**
     * return a block with the given id
     *
     * @param  $id
     * @return \Sonata\BlockBundle\Model\BlockInterface
     */
    function getBlock($id);

    /**
     * return a flat list if page's blocks
     *
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return array
     */
    function getBlocksById(PageInterface $page);

    /**
     * load blocks attached the given page
     *
     * @param PageInterface $page
     * @return array $blocks
     */
    function loadPageBlocks(PageInterface $page);

    /**
     * save the block
     *
     * @param array $data
     * @return bool
     */
    function saveBlocksPosition(array $data = array());

    /**
     * @param array $values
     * @return \Sonata\BlockBundle\Model\BlockInterface
     */
    function createNewContainer(array $values = array());
}