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

namespace Sonata\PageBundle\Entity;

use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;

/**
 * @extends BaseEntityManager<PageBlockInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockManager extends BaseEntityManager implements BlockManagerInterface
{
    public function updatePosition($id, int $position): PageBlockInterface
    {
        $block = $this->find($id);

        if (null === $block) {
            throw new \RuntimeException(\sprintf('Unable to update position to block with id %s', $id));
        }

        $block->setPosition($position);

        $this->save($block, false);

        return $block;
    }
}
