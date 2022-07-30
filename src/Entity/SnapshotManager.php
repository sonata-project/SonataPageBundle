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

use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyFactoryInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyInterface;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * This class manages SnapshotInterface persistency with the Doctrine ORM.
 *
 * @extends BaseEntityManager<SnapshotInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SnapshotManager extends BaseEntityManager implements SnapshotManagerInterface
{
    protected array $children = [];

    protected SnapshotPageProxyFactoryInterface $snapshotPageProxyFactory;

    /**
     * @param string          $class    Namespace of entity class
     * @param ManagerRegistry $registry An entity manager instance
     */
    public function __construct($class, ManagerRegistry $registry, SnapshotPageProxyFactoryInterface $snapshotPageProxyFactory)
    {
        parent::__construct($class, $registry);

        $this->snapshotPageProxyFactory = $snapshotPageProxyFactory;
    }

    public function enableSnapshots(array $snapshots, ?\DateTimeInterface $date = null): void
    {
        if (0 === \count($snapshots)) {
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

        $qb = $this->getRepository()->createQueryBuilder('s');
        $q = $qb->update()
            ->set('s.publicationDateEnd', ':date_end')
            ->where($qb->expr()->notIn('s.id', $snapshotIds))
            ->andWhere($qb->expr()->in('s.page', $pageIds))
            ->andWhere($qb->expr()->isNull('s.publicationDateEnd'))
            ->setParameter('date_end', $date, 'datetime')
            ->getQuery();

        $q->execute();
    }

    public function findEnableSnapshot(array $criteria)
    {
        $date = new \DateTime();
        $parameters = [
            'publicationDateStart' => $date,
            'publicationDateEnd' => $date,
        ];

        $query = $this->getRepository()
            ->createQueryBuilder('s')
            ->andWhere('s.publicationDateStart <= :publicationDateStart AND ( s.publicationDateEnd IS NULL OR s.publicationDateEnd >= :publicationDateEnd )')
            ->andWhere('s.enabled = true');

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

    public function cleanup(PageInterface $page, $keep)
    {
        if (!is_numeric($keep)) {
            throw new \RuntimeException(sprintf('Please provide an integer value, %s given', \gettype($keep)));
        }

        $innerQb = $this->getRepository()->createQueryBuilder('i');
        $expr = $innerQb->expr();

        // try a better Function expression for this?
        $ifNullExpr = sprintf(
            'CASE WHEN %s THEN 1 ELSE 0 END',
            $expr->isNull('i.publicationDateEnd')
        );

        // Subquery DQL doesn't support Limit
        $innerQb
            ->select('i.id')
            ->where($expr->eq('i.page', $page->getId()))
            ->orderBy($ifNullExpr, Criteria::DESC)
            ->addOrderBy('i.publicationDateEnd', Criteria::DESC)
            ->setMaxResults($keep);

        $query = $innerQb->getQuery();
        $innerArray = $query->getSingleColumnResult();

        $qb = $this->getRepository()->createQueryBuilder('s');
        $expr = $qb->expr();
        $qb->delete()
            ->where($expr->eq('s.page', $page->getId()))
            ->andWhere($expr->notIn(
                's.id',
                $innerArray
            ));

        return $qb->getQuery()->execute();
    }

    public function createSnapshotPageProxy(TransformerInterface $transformer, SnapshotInterface $snapshot): SnapshotPageProxyInterface
    {
        return $this->snapshotPageProxyFactory
            ->create($this, $transformer, $snapshot);
    }
}
