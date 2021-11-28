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

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\Doctrine\Model\ManagerInterface;

/**
 * This class manages BlockInterface persistency with the Doctrine ORM.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockManager extends BaseEntityManager implements ManagerInterface
{
    public function save($entity, $andFlush = true)
    {
        parent::save($entity, $andFlush);

        return $entity;
    }

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
    public function updatePosition($id, $position, $parentId = null, $pageId = null, $partial = true)
    {
        if ($partial) {
            $meta = $this->getEntityManager()->getClassMetadata($this->getClass());

            // retrieve object references
            $block = $this->getEntityManager()->getReference($this->getClass(), $id);
            $pageRelation = $meta->getAssociationMapping('page');
            $page = $this->getEntityManager()->getPartialReference($pageRelation['targetEntity'], $pageId);

            $parentRelation = $meta->getAssociationMapping('parent');
            $parent = $this->getEntityManager()->getPartialReference($parentRelation['targetEntity'], $parentId);

            $block->setPage($page);
            $block->setParent($parent);
        } else {
            $block = $this->find($id);
        }

        // set new values
        $block->setPosition($position);
        $this->getEntityManager()->persist($block);

        return $block;
    }
}
