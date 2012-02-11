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

use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\Template;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\Page;

use Doctrine\ORM\EntityManager;

class PageManager implements PageManagerInterface
{
    protected $entityManager;

    protected $class;

    protected $pageDefaults;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param $class
     * @param array $pageDefaults
     */
    public function __construct(EntityManager $entityManager, $class, array $pageDefaults)
    {
        $this->entityManager = $entityManager;
        $this->class         = $class;
        $this->pageDefaults  = $pageDefaults;
    }

    protected function getRepository()
    {
        return $this->entityManager->getRepository($this->class);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageByUrl(SiteInterface $site, $url)
    {
        return $this->findOneBy(array('url' => $url, 'site' => $site->getId()));
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $defaults = array())
    {
        // create a new page for this routing
        $class = $this->getClass();

        $page = new $class;

        foreach (array_merge($this->pageDefaults, $defaults) as $key => $value) {
            $method = 'set' . ucfirst($key);
            $page->$method($value);
        }

        return $page;
    }

    /**
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return void
     */
    public function fixUrl(PageInterface $page)
    {
        // hybrid page cannot be altered
        if (!$page->isHybrid()) {
            if (!$page->getSlug()) {
                $page->setSlug(Page::slugify($page->getName()));
            }

            // make sure Page has a valid url
            if ($page->getParent()) {
                $base = $page->getParent()->getUrl() == '/' ? '/' : $page->getParent()->getUrl().'/';
                $page->setUrl($base.$page->getSlug()) ;
            } else {
                $page->setUrl('/'.$page->getSlug());
            }
        }

        foreach ($page->getChildren() as $child) {
            $this->fixUrl($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(PageInterface $page)
    {
        if (!$page->isHybrid() || $page->getRouteName() == 'homepage') {
            $this->fixUrl($page);
        }

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria = array())
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria = array())
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function loadPages(SiteInterface $site)
    {
        $pages = $this->entityManager
            ->createQuery(sprintf('SELECT p FROM %s p INDEX BY p.id WHERE p.site = %d ORDER BY p.position ASC', $this->class, $site->getId()))
            ->execute();

        foreach ($pages as $page) {
            $parent = $page->getParent();

            $page->disableChildrenLazyLoading();
            if (!$parent) {
                continue;
            }

            $pages[$parent->getId()]->disableChildrenLazyLoading();
            $pages[$parent->getId()]->addChildren($page);
        }

        return $pages;
    }

    /**
     * {@inheritdoc}
     */
    public function getHybridPages(SiteInterface $site)
    {
        return $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from( $this->class, 'p')
            ->where('p.routeName <> :routeName and p.site = :site')
            ->setParameters(array(
                'routeName' => PageInterface::PAGE_ROUTE_CMS_NAME,
                'site' => $site->getId()
            ))
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }
}