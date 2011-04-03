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


interface BlockManagerInterface
{

    /**
     * return a block with the given id
     * 
     * @param  $id
     * @return BlockInterface
     */
    function getBlock($id);

    /**
     * return a flat list if page's blocks
     *
     * @param  $page
     * @return
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
     * @param $options
     * @return BlockInterface
     */
    function createNewContainer(array $values = array());

    /**
     * @abstract
     * @param PageInterface $object
     * @return void
     */
    function save(BlockInterface $object);
}