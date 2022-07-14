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

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Doctrine\Model\ManagerInterface;

/**
 * @extends ManagerInterface<PageBlockInterface>
 */
interface BlockManagerInterface extends ManagerInterface
{
    /**
     * Updates position for given block.
     *
     * @param int  $id       Block Id
     * @param int  $position New Position
     * @param int  $parentId Parent block Id (needed when partial = true)
     * @param int  $pageId   Page Id (needed when partial = true)
     * @param bool $partial  Should we use partial references? (Better for performance, but can lead to query issues.)
     *
     * @return BlockInterface
     */
    public function updatePosition($id, $position, $parentId = null, $pageId = null, $partial = true);
}
