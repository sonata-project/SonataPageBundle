<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Entity;

use Doctrine\Common\Persistence\ManagerRegistry;
use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Model\SnapshotPageProxyFactory;
use Sonata\PageBundle\Model\SnapshotPageProxyFactoryInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyInterface;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * This class manages SnapshotInterface persistency with the Doctrine ORM.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SnapshotManager extends BaseEntityManager implements SnapshotManagerInterface
{
    /**
     * @var array
     */
    protected $children = [];

    /**
     * @var array
     */
    protected $templates = [];

    /**
     * @var SnapshotPageProxyFactoryInterface
     */
    protected $snapshotPageProxyFactory;

    /**
     * @param string                            $class                    Namespace of entity class
     * @param ManagerRegistry                   $registry                 An entity manager instance
     * @param array                             $templates                An array of templates
     * @param SnapshotPageProxyFactoryInterface $snapshotPageProxyFactory
     */
    public function __construct($class, ManagerRegistry $registry, $templates = [], SnapshotPageProxyFactoryInterface $snapshotPageProxyFactory = null)
    {
        parent::__construct($class, $registry);

        // NEXT_MAJOR: make $snapshotPageProxyFactory parameter required
        if (null === $snapshotPageProxyFactory) {
            @trigger_error(
                'The $snapshotPageProxyFactory parameter is required with the next major release.',
                E_USER_DEPRECATED
            );
            $snapshotPageProxyFactory = new SnapshotPageProxyFactory('Sonata\PageBundle\Model\SnapshotPageProxy');
        }

        $this->templates = $templates;
        $this->snapshotPageProxyFactory = $snapshotPageProxyFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save($snapshot, $andFlush = true)
    {
        parent::save($snapshot);

        return $snapshot;
    }

    /**
     * {@inheritdoc}
     */
    public function enableSnapshots(array $snapshots, \DateTime $date = null)
    {
        if (0 == count($snapshots)) {
            return;
        }

        $date = $date ?: new \DateTime();
        $pageIds = $snapshotIds = [];

        foreach ($snapshots as $snapshot) {
            $pageIds[] = $snapshot->getPage()->getId();
            $snapshotIds[] = $snapshot->getId();

            $snapshot->setPublicationDateStart($date);
            $snapshot->setPublicationDateEnd(null);

            $this->getEntityManager()->persist($snapshot);
        }

        $this->getEntityManager()->flush();
        //@todo: strange sql and low-level pdo usage: use dql or qb
        $sql = sprintf("UPDATE %s SET publication_date_end = '%s' WHERE id NOT IN(%s) AND page_id IN (%s)",
            $this->getTableName(),
            $date->format('Y-m-d H:i:s'),
            implode(',', $snapshotIds),
            implode(',', $pageIds)
        );

        $this->getConnection()->query($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function findEnableSnapshot(array $criteria)
    {
        $date = new \Datetime();
        $parameters = [
            'publicationDateStart' => $date,
            'publicationDateEnd' => $date,
        ];

        $query = $this->getRepository()
            ->createQueryBuilder('s')
            ->andWhere('s.publicationDateStart <= :publicationDateStart AND ( s.publicationDateEnd IS NULL OR s.publicationDateEnd >= :publicationDateEnd )')
            ->andWhere('s.enabled = true')
        ;

        if (isset($criteria['site'])) {
            $query->andWhere('s.site = :site');
            $parameters['site'] = $criteria['site'];
        }

        if (isset($criteria['pageId'])) {
            $query->andWhere('s.page = :page');
            $parameters['page'] = $criteria['pageId'];
        } elseif (isset($criteria['url'])) {
            $query->andWhere('s.url = :url');
            $parameters['url'] = $criteria['url'];
        } elseif (isset($criteria['routeName'])) {
            $query->andWhere('s.routeName = :routeName');
            $parameters['routeName'] = $criteria['routeName'];
        } elseif (isset($criteria['pageAlias'])) {
            $query->andWhere('s.pageAlias = :pageAlias');
            $parameters['pageAlias'] = $criteria['pageAlias'];
        } elseif (isset($criteria['name'])) {
            $query->andWhere('s.name = :name');
            $parameters['name'] = $criteria['name'];
        } else {
            throw new \RuntimeException('please provide a `pageId`, `url`, `routeName` or `name` as criteria key');
        }

        $query->setMaxResults(1);
        $query->setParameters($parameters);

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * return a page with the given routeName.
     *
     * @param string $routeName
     *
     * @return PageInterface|false
     *
     * @deprecated since 3.2, to be removed in 4.0
     */
    public function getPageByName($routeName)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.2 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $snapshots = $this->getEntityManager()->createQueryBuilder()
            ->select('s')
            ->from($this->class, 's')
            ->where('s.routeName = :routeName')
            ->setParameters([
                'routeName' => $routeName,
            ])
            ->getQuery()
            ->execute();

        $snapshot = count($snapshots) > 0 ? $snapshots[0] : false;

        if ($snapshot) {
            return new SnapshotPageProxy($this, $snapshot);
        }

        return false;
    }

    /**
     * @param array $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param string $code
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    public function getTemplate($code)
    {
        if (!isset($this->templates[$code])) {
            throw new \RuntimeException(sprintf('No template references with the code : %s', $code));
        }

        return $this->templates[$code];
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup(PageInterface $page, $keep)
    {
        if (!is_numeric($keep)) {
            throw new \RuntimeException(sprintf('Please provide an integer value, %s given', gettype($keep)));
        }

        $tableName = $this->getTableName();
        $platform = $this->getConnection()->getDatabasePlatform()->getName();

        if ('mysql' === $platform) {
            return $this->getConnection()->exec(sprintf(
                'DELETE FROM %s
                WHERE
                    page_id = %d
                    AND id NOT IN (
                        SELECT id
                        FROM (
                            SELECT id, publication_date_end
                            FROM %s
                            WHERE
                                page_id = %d
                            ORDER BY
                                publication_date_end IS NULL DESC,
                                publication_date_end DESC
                            LIMIT %d
                        ) AS table_alias
                )',
                $tableName,
                $page->getId(),
                $tableName,
                $page->getId(),
                $keep
            ));
        }

        if ('oracle' === $platform) {
            return $this->getConnection()->exec(sprintf(
                'DELETE FROM %s
                WHERE
                    page_id = %d
                    AND id NOT IN (
                        SELECT id
                        FROM (
                            SELECT id, publication_date_end
                            FROM %s
                            WHERE
                                page_id = %d
                                AND rownum <= %d
                            ORDER BY publication_date_end DESC
                        ) table_alias
                )',
                $tableName,
                $page->getId(),
                $tableName,
                $page->getId(),
                $keep
            ));
        }

        throw new \RuntimeException(sprintf('The %s database platform has not been tested yet. Please report us if it works and feel free to create a pull request to handle it ;-)', $platform));
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = [])
    {
        $query = $this->getRepository()
            ->createQueryBuilder('s')
            ->select('s');

        $parameters = [];

        if (isset($criteria['enabled'])) {
            $query->andWhere('s.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        if (isset($criteria['site'])) {
            $query->join('s.site', 'si');
            $query->andWhere('si.id = :siteId');
            $parameters['siteId'] = $criteria['site'];
        }

        if (isset($criteria['page_id'])) {
            $query->join('s.page', 'p');
            $query->andWhere('p.id = :pageId');
            $parameters['pageId'] = $criteria['page_id'];
        }

        if (isset($criteria['parent'])) {
            $query->join('s.parent', 'pa');
            $query->andWhere('pa.id = :parentId');
            $parameters['parentId'] = $criteria['parent'];
        }

        if (isset($criteria['root'])) {
            $isRoot = (bool) $criteria['root'];
            if ($isRoot) {
                $query->andWhere('s.parent IS NULL');
            } else {
                $query->andWhere('s.parent IS NOT NULL');
            }
        }

        $query->setParameters($parameters);

        $pager = new Pager();
        $pager->setMaxPerPage($limit);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

        return $pager;
    }

    /**
     * Create a snapShotPageProxy instance.
     *
     * @param TransformerInterface $transformer
     * @param SnapshotInterface    $snapshot
     *
     * @return SnapshotPageProxyInterface
     */
    final public function createSnapshotPageProxy(TransformerInterface $transformer, SnapshotInterface $snapshot)
    {
        return $this->snapshotPageProxyFactory
            ->create($this, $transformer, $snapshot)
        ;
    }
}
