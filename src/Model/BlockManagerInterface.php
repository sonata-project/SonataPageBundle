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
     * @param int|string $id
     */
    public function updatePosition($id, int $position): PageBlockInterface;
}
