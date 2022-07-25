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
 * This class manages BlockInterface persistency with the Doctrine ORM.
 *
 * @extends BaseEntityManager<PageBlockInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class BlockManager extends BaseEntityManager implements BlockManagerInterface
{
    public function updatePosition($id, $position, $parentId = null, $pageId = null, $partial = true)
    {
        if ($partial) {
            $meta = $this->getEntityManager()->getClassMetadata($this->getClass());

            // retrieve object references
            $block = $this->getEntityManager()->getReference($this->getClass(), $id);
            $pageRelation = $meta->getAssociationMapping('page');
            /** @var class-string<PageInterface> */
            $pageClassName = $pageRelation['targetEntity'];

            $page = $this->getEntityManager()->getPartialReference($pageClassName, $pageId);

            $parentRelation = $meta->getAssociationMapping('parent');
            /** @var class-string<PageBlockInterface> */
            $parentClassName = $pageRelation['targetEntity'];

            $parent = $this->getEntityManager()->getPartialReference($parentClassName, $parentId);

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
