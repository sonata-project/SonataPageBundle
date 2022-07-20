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

/**
 * BlockInteractorInterface.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface BlockInteractorInterface
{
    /**
     * return a block with the given id.
     *
     * @return PageBlockInterface
     */
    public function getBlock($id);

    /**
     * return a flat list if page's blocks.
     *
     * @return PageBlockInterface[]
     */
    public function getBlocksById(PageInterface $page);

    /**
     * load blocks attached the given page.
     *
     * @return array $blocks
     */
    public function loadPageBlocks(PageInterface $page);

    /**
     * save the blocks positions.
     *
     * @param bool $partial Should we use partial references? (Better for performance, but can lead to query issues.)
     *
     * @return bool
     */
    public function saveBlocksPosition(array $data = [], $partial = true);

    /**
     * @param array    $values An array of values for container creation
     * @param \Closure $alter  A closure to alter container created
     *
     * @return PageBlockInterface
     */
    public function createNewContainer(array $values = [], ?\Closure $alter = null);
}
