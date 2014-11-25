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
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;


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
     * Updates position for given block
     *
     * @param int  $id        Block Id
     * @param int  $position  New Position
     * @param int  $parentId  Parent block Id (needed when partial = true)
     * @param int  $pageId    Page Id (needed when partial = true)
     * @param bool $partial   Should we use partial references? (Better for performance, but can lead to query issues.)
     *
     * @return mixed
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

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        $query = $this->getRepository()
            ->createQueryBuilder('b')
            ->select('b');

        $parameters = array();

        if (isset($criteria['enabled'])) {
            $query->andWhere('p.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        if (isset($criteria['type'])) {
            $query->andWhere('p.type = :type');
            $parameters['type'] = $criteria['type'];
        }

        $query->setParameters($parameters);

        $pager = new Pager();
        $pager->setMaxPerPage($limit);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

        return $pager;
    }
}
