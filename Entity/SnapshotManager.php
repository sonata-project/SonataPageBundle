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

use Sonata\BlockBundle\Model\BlockInterface;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\Template;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;

use Sonata\PageBundle\Model\SnapshotPageProxy;

/**
 * This class manages SnapshotInterface persistency with the Doctrine ORM
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SnapshotManager implements SnapshotManagerInterface
{
    protected $entityManager;

    protected $children = array();

    protected $class;

    protected $blockClass;

    protected $templates = array();

    /**
     * @param EntityManager $entityManager
     * @param string        $class
     * @param string        $blockClass
     * @param array         $templates
     */
    public function __construct(EntityManager $entityManager, $class, $blockClass, $templates = array())
    {
        $this->entityManager = $entityManager;
        $this->class         = $class;
        $this->blockClass    = $blockClass;
        $this->templates     = $templates;
    }

    /**
     * @return SnapshotInterface
     */
    public function create()
    {
        return new $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SnapshotInterface $snapshot)
    {
        $this->entityManager->persist($snapshot);
        $this->entityManager->flush();

        return $snapshot;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->entityManager->getRepository($this->class);
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->entityManager->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    public function enableSnapshots(array $snapshots)
    {
        if (count($snapshots) == 0) {
            return;
        }

        $now = new \DateTime;
        $pageIds = $snapshotIds = array();
        foreach ($snapshots as $snapshot) {
            $pageIds[] = $snapshot->getPage()->getId();
            $snapshotIds[] = $snapshot->getId();

            $snapshot->setPublicationDateStart($now);
            $snapshot->setPublicationDateEnd(null);

            $this->entityManager->persist($snapshot);
        }

        $this->entityManager->flush();
        //@todo: strange sql and low-level pdo usage: use dql or qb
        $sql = sprintf("UPDATE %s SET publication_date_end = '%s' WHERE id NOT IN(%s) AND page_id IN (%s) AND publication_date_end IS NULL",
            $this->entityManager->getClassMetadata($this->class)->table['name'],
            $now->format('Y-m-d H:i:s'),
            implode(',', $snapshotIds),
            implode(',', $pageIds)
        );

        $this->getConnection()->query($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findEnableSnapshot(array $criteria)
    {
        $date = new \Datetime;
        $parameters = array(
            'publicationDateStart'  => $date,
            'publicationDateEnd'    => $date,
        );
        $query = $this->getRepository()
            ->createQueryBuilder('s')
            ->andWhere('s.publicationDateStart <= :publicationDateStart AND ( s.publicationDateEnd IS NULL OR s.publicationDateEnd >= :publicationDateEnd )');

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

        try {
            return $query->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * return a page with the given routeName
     *
     * @param string $routeName
     *
     * @return \Sonata\PageBundle\Model\PageInterface|false
     */
    public function getPageByName($routeName)
    {
        $snapshots = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from($this->class, 's')
            ->where('s.routeName = :routeName')
            ->setParameters(array(
                'routeName' => $routeName
            ))
            ->getQuery()
            ->execute();

        $snapshot = count($snapshots) > 0 ? $snapshots[0] : false;

        if ($snapshot) {
            return new SnapshotPageProxy($this, $snapshot);
        }

        return false;
    }

    /**
     * Get snapshot
     *
     * @param integer $pageId
     *
     * @return \Sonata\PageBundle\Model\SnapshotInterface
     */
    public function getSnapshotByPageId($pageId)
    {
        if (!$pageId) {
            return null;
        }

        $date = new \Datetime;
        $parameters = array(
            'publicationDateStart'  => $date,
            'publicationDateEnd'    => $date,
            'pageId'                => $pageId
        );

        try {
            $snapshot = $this->entityManager->createQueryBuilder()
                ->select('s')
                ->from($this->class, 's')
                ->where('s.page = :pageId and s.enabled = 1')
                ->andWhere('s.publicationDateStart <= :publicationDateStart AND ( s.publicationDateEnd IS NULL OR s.publicationDateEnd >= :publicationDateEnd )')
                ->setParameters($parameters)
                ->getQuery()
                ->getSingleResult();

        } catch (NoResultException $e) {
            $snapshot = null;
        }

        return $snapshot;
    }

    /**
     * Get page by id
     *
     * @param integer $id
     *
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageById($id)
    {
        $snapshot = $this->getSnapshotByPageId($id);

        return $snapshot ? new SnapshotPageProxy($this, $snapshot) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren(PageInterface $parent)
    {
        if (!isset($this->children[$parent->getId()])) {
            $date = new \Datetime;
            $parameters = array(
                'publicationDateStart'  => $date,
                'publicationDateEnd'    => $date,
                'parentId'              => $parent->getId(),
            );

            $snapshots = $this->entityManager->createQueryBuilder()
                ->select('s')
                ->from($this->class, 's')
                ->where('s.parentId = :parentId and s.enabled = 1')
                ->andWhere('s.publicationDateStart <= :publicationDateStart AND ( s.publicationDateEnd IS NULL OR s.publicationDateEnd >= :publicationDateEnd )')
                ->orderBy('s.position')
                ->setParameters($parameters)
                ->getQuery()
                ->execute();

            $pages = array();

            foreach ($snapshots as $snapshot) {
                $page = new SnapshotPageProxy($this, $snapshot);
                $pages[$page->getId()] = $page;
            }

            $this->children[$parent->getId()] = new \Doctrine\Common\Collections\ArrayCollection($pages);
        }

        return $this->children[$parent->getId()];
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
     * @throws \RunTimeException
     */
    public function getTemplate($code)
    {
        if (!isset($this->templates[$code])) {
            throw new \RunTimeException(sprintf('No template references with the code : %s', $code));
        }

        return $this->templates[$code];
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}
