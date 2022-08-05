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
use Sonata\PageBundle\Model\PageInterface;

/**
 * @extends BaseEntityManager<PageBlockInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockManager extends BaseEntityManager implements BlockManagerInterface
{
    public function updatePosition($id, $position, $parentId = null, $pageId = null, $partial = true)
    {
        $entityManager = $this->getEntityManager();

        if ($partial) {
            $meta = $entityManager->getClassMetadata($this->getClass());
            $block = $entityManager->getReference($this->getClass(), $id);

            if (null === $block) {
                throw new \RuntimeException(sprintf('Unable to update position to block with id %s', $id));
            }

            $pageRelation = $meta->getAssociationMapping('page');
            /** @var class-string<PageInterface> */
            $pageClassName = $pageRelation['targetEntity'];

            $page = $entityManager->getPartialReference($pageClassName, $pageId);

            /** @var class-string<PageBlockInterface> */
            $parentClassName = $pageRelation['targetEntity'];

            $parent = $entityManager->getPartialReference($parentClassName, $parentId);

            $block->setPage($page);
            $block->setParent($parent);
        } else {
            $block = $this->find($id);

            if (null === $block) {
                throw new \RuntimeException(sprintf('Unable to update position to block with id %s', $id));
            }
        }

        // set new values
        $block->setPosition($position);
        $entityManager->persist($block);

        return $block;
    }
}
