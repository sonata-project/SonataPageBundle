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

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\Template;
use Sonata\PageBundle\Model\SnapshotChildrenCollection;

use Application\Sonata\PageBundle\Entity\Page;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;

use Sonata\PageBundle\Model\SnapshotPageProxy;

class SnapshotManager implements SnapshotManagerInterface
{
    protected $entityManager;

    protected $children = array();

    protected $class;

    protected $templates = array();

    public function __construct(EntityManager $entityManager, $class = 'Application\Sonata\PageBundle\Entity\Snapshot', $templates = array())
    {
        $this->entityManager = $entityManager;
        $this->class         = $class;
        $this->templates     = $templates;
    }

    /**
     * @param SnapshotInterface $snapshot
     * @return SnapshotInterface
     */
    public function save(SnapshotInterface $snapshot)
    {
        $this->entityManager->persist($snapshot);
        $this->entityManager->flush();

        return $snapshot;
    }

    protected function getRepository()
    {
        return $this->entityManager->getRepository($this->class);
    }

    public function getConnection()
    {
        return $this->entityManager->getConnection();
    }

    /**
     * Enabled a snapshot - make it public
     *
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param array $snapshots
     * @return
     */
    public function enableSnapshots(SiteInterface $site, $snapshots)
    {
        if (!is_array($snapshots)) {
            $snapshots = array($snapshots);
        }

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
        $sql = sprintf("UPDATE %s SET publication_date_end = '%s' WHERE id NOT IN(%s) AND page_id IN (%s) AND publication_date_end IS NULL AND site_id = %d",
            $this->entityManager->getClassMetadata('Application\Sonata\PageBundle\Entity\Snapshot')->table['name'],
            $now->format('Y-m-d H:i:s'),
            implode(',', $snapshotIds),
            implode(',', $pageIds),
            $site->getId()
        );

        $this->getConnection()->query($sql);
    }

    /**
     * @param array $criteria
     * @return array
     */
    public function findBy(array $criteria = array())
    {
        return $this->getRepository()->findBy($criteria);
    }

    public function findEnableSnapshot(array $criteria = array())
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
        } elseif (isset($criteria['name'])) {
            $query->andWhere('s.name = :name');
            $parameters['name'] = $criteria['name'];
        } else {
            throw new \RuntimeException('please provide a `pageId`, `url`, `routeName` or `name` as criteria key');
        }

        $query->setParameters($parameters);

        try {
            return $query->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
    }

    /**
     * @param array $criteria
     * @return SnapshotInterface
     */
    public function findOneBy(array $criteria = array())
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @param \Sonata\PageBundle\Model\SnapshotInterface $snapshot
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function load(SnapshotInterface $snapshot)
    {
        $page = new Page;

        $page->setRouteName($snapshot->getRouteName());
        $page->setCustomUrl($snapshot->getUrl());
        $page->setUrl($snapshot->getUrl());
        $page->setPosition($snapshot->getPosition());
        $page->setDecorate($snapshot->getDecorate());
        $page->setSite($snapshot->getSite());

        $content = json_decode($snapshot->getContent(), true);

        $page->setId($content['id']);
        $page->setJavascript($content['javascript']);
        $page->setStylesheet($content['stylesheet']);
        $page->setRawHeaders($content['raw_headers']);
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
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return \Sonata\PageBundle\Model\BlockInterface
     */
    public function loadBlock(array $content, PageInterface $page)
    {
        $block = new \Application\Sonata\PageBundle\Entity\Block;

        $block->setPage($page);
        $block->setId($content['id']);
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
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return \Sonata\PageBundle\Model\SnapshotInterface
     */
    public function create(PageInterface $page)
    {
        $snapshot = new \Application\Sonata\PageBundle\Entity\Snapshot();

        $snapshot->setPage($page);
        $snapshot->setUrl($page->getUrl());
        $snapshot->setEnabled($page->getEnabled());
        $snapshot->setRouteName($page->getRouteName());
        $snapshot->setName($page->getName());
        $snapshot->setPosition($page->getPosition());
        $snapshot->setDecorate($page->getDecorate());
        $snapshot->setSite($page->getSite());

        if ($page->getParent()) {
            $snapshot->setParentId($page->getParent()->getId());
        }

        if ($page->getTarget()) {
            $snapshot->setTargetId($page->getTarget()->getId());
        }

        $content = array();
        $content['id']                = $page->getId();
        $content['name']              = $page->getName();
        $content['javascript']        = $page->getJavascript();
        $content['stylesheet']        = $page->getStylesheet();
        $content['raw_headers']       = $page->getRawHeaders();
        $content['meta_description']  = $page->getMetaDescription();
        $content['meta_keyword']      = $page->getMetaKeyword();
        $content['template_code']     = $page->getTemplateCode();
        $content['request_method']    = $page->getRequestMethod();
        $content['created_at']        = $page->getCreatedAt()->format('U');
        $content['updated_at']        = $page->getUpdatedAt()->format('U');
        $content['slug']              = $page->getSlug();
        $content['sites']             = $page->getSite() ? $page->getSite()->getId() : false;
        $content['parent_id']         = $page->getParent() ? $page->getParent()->getId() : false;
        $content['target_id']         = $page->getTarget() ? $page->getTarget()->getId() : false;

        $content['blocks'] = array();
        foreach ($page->getBlocks() as $block) {
            $content['blocks'][] = $this->createBlocks($block);
        }

        $snapshot->setContent(json_encode($content));

        return $snapshot;
    }

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return array
     */
    public function createBlocks(BlockInterface $block)
    {
        $content = array();
        $content['id']       = $block->getId();
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
     * @return \Sonata\PageBundle\Model\PageInterface|false
     */
    public function getPageByName($routeName)
    {
        $snapshots = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from('Application\Sonata\PageBundle\Entity\Snapshot', 's')
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
                ->from('Application\Sonata\PageBundle\Entity\Snapshot', 's')
                ->where('s.page = :pageId and s.enabled = 1')
                ->andWhere('s.publicationDateStart <= :publicationDateStart AND ( s.publicationDateEnd IS NULL OR s.publicationDateEnd >= :publicationDateEnd )')
                ->setParameters($parameters)
                ->getQuery()
                ->getSingleResult();

        } catch(NoResultException $e) {
            $snapshot = null;
        }

        return $snapshot;
    }

    /**
     * Get page by id
     *
     * @param integer $id
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getPageById($id)
    {
        $snapshot = $this->getSnapshotByPageId($id);

        return $snapshot ? new SnapshotPageProxy($this, $snapshot) : null;
    }

    /**
     * Get children
     *
     * @param \Sonata\PageBundle\Model\PageInterface $parent
     * @return Collection
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
                ->from('Application\Sonata\PageBundle\Entity\Snapshot', 's')
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

    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    public function getTemplate($code)
    {
        if (!isset($this->templates[$code])) {
            throw new \RunTimeException(sprintf('No template references with the code : %s', $code));
        }

        return $this->templates[$code];
    }

    public function getClass()
    {
        return $this->class;
    }
}
