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

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

/**
 * This class manages SiteInterface persistency with the Doctrine ORM
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SiteManager extends BaseEntityManager implements SiteManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function save($site, $andFlush = true)
    {
        parent::save($site, $andFlush);

        return $site;
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        $query = $this->getRepository()
            ->createQueryBuilder('s')
            ->select('s');

        $fields = $this->getEntityManager()->getClassMetadata($this->class)->getFieldNames();
        foreach ($sort as $field => $direction) {
            if (!in_array($field, $fields)) {
                throw new \RuntimeException(sprintf("Invalid sort field '%s' in '%s' class", $field, $this->class));
            }
        }
        if (count($sort) == 0) {
            $sort = array('name' => 'ASC');
        }
        foreach ($sort as $field => $direction) {
            $query->orderBy(sprintf('s.%s', $field), strtoupper($direction));
        }

        $parameters = array();

        if (isset($criteria['enabled'])) {
            $query->andWhere('s.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        if (isset($criteria['is_default'])) {
            $query->andWhere('s.isDefault = :isDefault');
            $parameters['isDefault'] = $criteria['is_default'];
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
