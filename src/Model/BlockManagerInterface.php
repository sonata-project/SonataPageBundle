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

use Sonata\Doctrine\Model\ManagerInterface;

/**
 * @extends ManagerInterface<PageBlockInterface>
 */
interface BlockManagerInterface extends ManagerInterface
{
    /**
     * Updates position for given block.
     *
     * @param int|string      $id       Block Id
     * @param int             $position New Position
     * @param int|string|null $parentId Parent block Id (needed when partial = true)
     * @param int|string|null $pageId   Page Id (needed when partial = true)
     * @param bool            $partial  Should we use partial references? (Better for performance, but can lead to query issues.)
     *
     * @return PageBlockInterface
     */
    public function updatePosition($id, $position, $parentId = null, $pageId = null, $partial = true);
}
