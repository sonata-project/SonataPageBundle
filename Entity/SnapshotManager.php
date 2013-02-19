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

    protected $pageClass;

    protected $blockClass;

    protected $templates = array();

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param string                      $class
     * @param string                      $pageClass
     * @param string                      $blockClass
     * @param array                       $templates
     */
    public function __construct(EntityManager $entityManager, $class, $pageClass, $blockClass, $templates = array())
    {
        $this->entityManager = $entityManager;
        $this->class         = $class;
        $this->pageClass     = $pageClass;
        $this->blockClass    = $blockClass;
        $this->templates     = $templates;
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
        $pageRefs = $snapshotIds = array();

        // enable the new snapshots
        $pageMapping = $this->entityManager->getClassMetadata($this->class)->getAssociationMapping('page');
        foreach ($snapshots as $snapshot) {
            $pageRefs[$snapshot->getPage()->getId()] = $this->entityManager->getReference($pageMapping['targetEntity'], $snapshot->getPage()->getId());
            $snapshotIds[] = $snapshot->getId();

            $snapshot->setPublicationDateStart(clone $now);
            $snapshot->setPublicationDateEnd(null);

            $this->entityManager->persist($snapshot);
        }
        $this->entityManager->flush();

        // fetch the previously used snapshots
        $qb = $this->entityManager->createQueryBuilder();
        $qb->from($this->class, 's')
            ->select('s')
            ->where($qb->expr()->andX(
                $qb->expr()->notIn('s.id', ':shapshotIds'),
                $qb->expr()->in('s.page', ':pageRefs'),
                $qb->expr()->orX(
                    $qb->expr()->eq('s.enabled', ':enabled'),
                    $qb->expr()->isNull('s.publicationDateEnd')
                )
            ))
            ->setParameters(array(
                'shapshotIds' => $snapshotIds,
                'pageRefs' => $pageRefs,
                'enabled' => 1
            ))
        ;
        $snapshots = $qb->getQuery()->execute();

        // disable the previously used snapshots
        if (count($snapshots)) {
            foreach ($snapshots as $snapshot) {
                if ($snapshot->getEnabled()) {
                    $snapshot->setEnabled(false);
                }

                if (!$snapshot->getPublicationDateEnd()) {
                    $snapshot->setPublicationDateEnd(clone $now);
                }
            }

            $this->entityManager->flush();
        }
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
     * {@inheritdoc}
     */
    public function load(SnapshotInterface $snapshot)
    {
        $page = new $this->pageClass;

        $page->setRouteName($snapshot->getRouteName());
        $page->setPageAlias($snapshot->getPageAlias());
        $page->setType($snapshot->getType());
        $page->setCustomUrl($snapshot->getUrl());
        $page->setUrl($snapshot->getUrl());
        $page->setPosition($snapshot->getPosition());
        $page->setDecorate($snapshot->getDecorate());
        $page->setSite($snapshot->getSite());
        $page->setEnabled($snapshot->getEnabled());

        $content = $this->fixPageContent($snapshot->getContent());

        $page->setId($content['id']);
        $page->setJavascript($content['javascript']);
        $page->setStylesheet($content['stylesheet']);
        $page->setRawHeaders($content['raw_headers']);
        $page->setTitle($content['title']);
        $page->setMetaDescription($content['meta_description']);
        $page->setMetaKeyword($content['meta_keyword']);
        $page->setName($content['name']);
        $page->setSlug($content['slug']);
        $page->setTemplateCode($content['template_code']);
        $page->setRequestMethod($content['request_method']);

        $createdAt = new \DateTime;
        $createdAt->setTimestamp($content['created_at']);
        $page->setCreatedAt($createdAt);

        $updatedAt = new \DateTime;
        $updatedAt->setTimestamp($content['updated_at']);
        $page->setUpdatedAt($updatedAt);

        return $page;
    }

    /**
     * @param array $content
     *
     * @return array
     */
    protected function fixPageContent(array $content)
    {
        if (!array_key_exists('title', $content)) {
            $content['title'] = null;
        }

        return $content;
    }

    /**
     * @param array $content
     *
     * @return array
     */
    protected function fixBlockContent(array $content)
    {
        if (!array_key_exists('name', $content)) {
            $content['name'] = null;
        }

        return $content;
    }

    /**
     * @param array                                  $content
     * @param \Sonata\PageBundle\Model\PageInterface $page
     *
     * @return \Sonata\BlockBundle\Model\BlockInterface
     */
    public function loadBlock(array $content, PageInterface $page)
    {
        $block = new $this->blockClass;

        $content = $this->fixBlockContent($content);

        $block->setPage($page);
        $block->setId($content['id']);
        $block->setName($content['name']);
        $block->setEnabled($content['enabled']);
        $block->setPosition($content['position']);
        $block->setSettings($content['settings']);
        $block->setType($content['type']);

        $createdAt = new \DateTime;
        $createdAt->setTimestamp($content['created_at']);
        $block->setCreatedAt($createdAt);

        $updatedAt = new \DateTime;
        $updatedAt->setTimestamp($content['updated_at']);
        $block->setUpdatedAt($updatedAt);

        foreach ($content['blocks'] as $child) {
            $block->addChildren($this->loadBlock($child, $page));
        }

        return $block;
    }

    /**
     * {@inheritdoc}
     */
    public function create(PageInterface $page)
    {
        $snapshot = new $this->class;

        $snapshot->setPage($page);
        $snapshot->setUrl($page->getUrl());
        $snapshot->setEnabled($page->getEnabled());
        $snapshot->setRouteName($page->getRouteName());
        $snapshot->setPageAlias($page->getPageAlias());
        $snapshot->setType($page->getType());
        $snapshot->setName($page->getName());
        $snapshot->setPosition($page->getPosition());
        $snapshot->setDecorate($page->getDecorate());

        if (!$page->getSite()) {
            throw new \RuntimeException(sprintf('No site linked to the page.id=%s', $page->getId()));
        }

        $snapshot->setSite($page->getSite());

        if ($page->getParent()) {
            $snapshot->setParentId($page->getParent()->getId());
        }

        if ($page->getTarget()) {
            $snapshot->setTargetId($page->getTarget()->getId());
        }

        $content                     = array();
        $content['id']               = $page->getId();
        $content['name']             = $page->getName();
        $content['javascript']       = $page->getJavascript();
        $content['stylesheet']       = $page->getStylesheet();
        $content['raw_headers']      = $page->getRawHeaders();
        $content['title']            = $page->getTitle();
        $content['meta_description'] = $page->getMetaDescription();
        $content['meta_keyword']     = $page->getMetaKeyword();
        $content['template_code']    = $page->getTemplateCode();
        $content['request_method']   = $page->getRequestMethod();
        $content['created_at']       = $page->getCreatedAt()->format('U');
        $content['updated_at']       = $page->getUpdatedAt()->format('U');
        $content['slug']             = $page->getSlug();
        $content['parent_id']        = $page->getParent() ? $page->getParent()->getId() : false;
        $content['target_id']        = $page->getTarget() ? $page->getTarget()->getId() : false;

        $content['blocks'] = array();
        foreach ($page->getBlocks() as $block) {
            $content['blocks'][] = $this->createBlocks($block);
        }

        $snapshot->setContent($content);

        return $snapshot;
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     *
     * @return array
     */
    public function createBlocks(BlockInterface $block)
    {
        $content = array();
        $content['id']       = $block->getId();
        $content['name']     = $block->getName();
        $content['enabled']  = $block->getEnabled();
        $content['position'] = $block->getPosition();
        $content['settings'] = $block->getSettings();
        $content['type']     = $block->getType();
        $content['created_at'] = $block->getCreatedAt()->format('U');
        $content['updated_at'] = $block->getUpdatedAt()->format('U');
        $content['blocks']   = array();

        foreach ($block->getChildren() as $child) {
            $content['blocks'][] = $this->createBlocks($child);
        }

        return $content;
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
