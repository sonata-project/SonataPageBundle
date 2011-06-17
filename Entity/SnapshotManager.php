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
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\BlockInterface;
use Application\Sonata\PageBundle\Entity\Page;
use Doctrine\ORM\EntityManager;
use Sonata\PageBundle\Model\SnapshotChildrenCollection;

class SnapshotManager implements SnapshotManagerInterface
{
    protected $entityManager;

    protected $children = array();

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        $class = 'Application\Sonata\PageBundle\Entity\Snapshot';
        if (class_exists($class)) {
            $this->repository = $this->entityManager->getRepository($class);
        }
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

    /**
     * @param array $criteria
     * @return array
     */
    public function findBy(array $criteria = array())
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * @param array $criteria
     * @return SnapshotInterface
     */
    public function findOneBy(array $criteria = array())
    {
        return $this->repository->findOneBy($criteria);
    }

      /**
     * @param \Sonata\PageBundle\Model\SnapshotInterface $snapshot
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function load(SnapshotInterface $snapshot)
    {
        $page = new \Application\Sonata\PageBundle\Entity\Page;

        $page->setRouteName($snapshot->getRouteName());
        $page->setCustomUrl($snapshot->getCustomUrl());
        $page->setPosition($snapshot->getPosition());
        $page->setPublicationDateEnd($snapshot->getPublicationDateEnd());
        $page->setPublicationDateStart($snapshot->getPublicationDateStart());
        $page->setSlug($snapshot->getSlug());
        $page->setDecorate($snapshot->getDecorate());

        $content = json_decode($snapshot->getContent(), true);
        $page->setId($content['id']);
        $page->setJavascript($content['javascript']);
        $page->setStylesheet($content['stylesheet']);
        $page->setMetaDescription($content['meta_description']);
        $page->setMetaKeyword($content['meta_keyword']);
        $page->setName($content['name']);

        $template = new \Application\Sonata\PageBundle\Entity\Template;
        $template->setPath($content['template']);

        $page->setTemplate($template);

        $createdAt = new \DateTime;
        $createdAt->setTimestamp($content['created_at']);
        $page->setCreatedAt($createdAt);

        $updatedAt = new \DateTime;
        $updatedAt->setTimestamp($content['updated_at']);
        $page->setUpdatedAt($updatedAt);

        foreach ($content['blocks'] as $block) {
            $page->addBlocks($this->loadBlock($block, $page));
        }

        $page->setChildren(new SnapshotChildrenCollection($this, $page));

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
        $snapshot->setEnabled($page->getEnabled());
        $snapshot->setRouteName($page->getRouteName());
        $snapshot->setCustomUrl($page->getCustomUrl());
        $snapshot->setPosition($page->getPosition());
        $snapshot->setPublicationDateEnd($page->getPublicationDateEnd());
        $snapshot->setPublicationDateStart($page->getPublicationDateStart());
        $snapshot->setSlug($page->getSlug());
        $snapshot->setDecorate($page->getDecorate());

        if ($page->getParent()) {
            $snapshot->setParentId($page->getParent()->getId());
        }

        $content = array();
        $content['id']                = $page->getId();
        $content['name']              = $page->getName();
        $content['javascript']        = $page->getJavascript();
        $content['stylesheet']        = $page->getStylesheet();
        $content['meta_description']  = $page->getMetaDescription();
        $content['meta_keyword']      = $page->getMetaKeyword();
        $content['template']          = $page->getTemplate()->getPath();
        $content['created_at']        = $page->getCreatedAt()->format('U');
        $content['updated_at']        = $page->getUpdatedAt()->format('U');

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
     * @return PageInterface|false
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

        if($snapshot) {
            return $this->load($snapshot);
        }

        return false;
    }

    public function getChildren(PageInterface $page)
    {
        if (!isset($this->children[$page->getId()])) {
            $snapshots = $this->entityManager->createQueryBuilder()
                ->select('s')
                ->from('Application\Sonata\PageBundle\Entity\Snapshot', 's')
                ->where('s.parentId = :parentId and s.enabled = 1')
                ->setParameters(array(
                    'parentId' => $page->getId()
                ))
                ->getQuery()
                ->execute();

            $pages = array();
            foreach($snapshots as $snapshot) {
                $page = $this->load($snapshot);
                $pages[$page->getId()] = $page;
            }

            $this->children[$page->getId()] = new \Doctrine\Common\Collections\ArrayCollection($pages);
        }

        return $this->children[$page->getId()];
    }
}