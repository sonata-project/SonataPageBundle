<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Entity;

use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\BlockBundle\Model\BlockInterface;

use Doctrine\ORM\EntityManager;
use Sonata\CoreBundle\Entity\DoctrineBaseManager;

/**
 * This class manages BlockInterface persistency with the Doctrine ORM
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockManager extends DoctrineBaseManager implements BlockManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function save($page, $andFlush = true)
    {
        parent::save($page, $andFlush);

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function updatePosition($id, $position, $parentId, $pageId)
    {
        $em = $this->entityManager;
        $meta = $em->getClassMetadata($this->getClass());

        // retrieve object references
        $block = $em->getReference($this->getClass(), $id);
        $pageRelation = $meta->getAssociationMapping('page');
        $page = $em->getPartialReference($pageRelation['targetEntity'], $pageId);

        $parentRelation = $meta->getAssociationMapping('parent');
        $parent = $em->getPartialReference($parentRelation['targetEntity'], $parentId);

        // set new values
        $block->setPosition($position);
        $block->setPage($page);
        $block->setParent($parent);
        $em->persist($block);

        return $block;
    }
}
