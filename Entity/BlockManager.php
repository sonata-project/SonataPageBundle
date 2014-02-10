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

use Sonata\CoreBundle\Model\BaseEntityManager;

/**
 * This class manages BlockInterface persistency with the Doctrine ORM
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockManager extends BaseEntityManager implements BlockManagerInterface
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

        $meta = $this->getEntityManager()->getClassMetadata($this->getClass());

        // retrieve object references
        $block = $this->getEntityManager()->getReference($this->getClass(), $id);
        $pageRelation = $meta->getAssociationMapping('page');
        $page = $this->getEntityManager()->getPartialReference($pageRelation['targetEntity'], $pageId);

        $parentRelation = $meta->getAssociationMapping('parent');
        $parent = $this->getEntityManager()->getPartialReference($parentRelation['targetEntity'], $parentId);

        // set new values
        $block->setPosition($position);
        $block->setPage($page);
        $block->setParent($parent);
        $this->getEntityManager()->persist($block);

        return $block;
    }
}
