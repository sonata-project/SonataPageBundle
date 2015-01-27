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

use Doctrine\Common\Persistence\ManagerRegistry;
use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\Page;
use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;

/**
 * This class manages PageInterface persistency with the Doctrine ORM
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PageManager extends BaseEntityManager implements PageManagerInterface
{
    /**
     * @var array
     */
    protected $pageDefaults;

    /**
     * @var array
     */
    protected $defaults;

    /**
     * @param string          $class
     * @param ManagerRegistry $registry
     * @param array           $defaults
     * @param array           $pageDefaults
     */
    public function __construct($class, ManagerRegistry $registry, array $defaults = array(), array $pageDefaults = array())
    {
        parent::__construct($class, $registry);

        $this->defaults     = $defaults;
        $this->pageDefaults = $pageDefaults;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageByUrl(SiteInterface $site, $url)
    {
        return $this->findOneBy(array(
            'url'  => $url,
            'site' => $site->getId()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        $query = $this->getRepository()
            ->createQueryBuilder('p')
            ->select('p');

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
            $query->orderBy(sprintf('p.%s', $field), strtoupper($direction));
        }

        $parameters = array();

        if (isset($criteria['enabled'])) {
            $query->andWhere('p.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        if (isset($criteria['edited'])) {
            $query->andWhere('p.edited = :edited');
            $parameters['edited'] = $criteria['edited'];
        }

        if (isset($criteria['site'])) {
            $query->join('p.site', 's');
            $query->andWhere('s.id = :siteId');
            $parameters['siteId'] = $criteria['site'];
        }

        if (isset($criteria['parent'])) {
            $query->join('p.parent', 'pa');
            $query->andWhere('pa.id = :parentId');
            $parameters['parentId'] = $criteria['parent'];
        }

        if (isset($criteria['root'])) {
            $isRoot = (bool) $criteria['root'];
            if ($isRoot) {
                $query->andWhere('p.parent IS NULL');
            } else {
                $query->andWhere('p.parent IS NOT NULL');
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
     * {@inheritdoc}
     */
    public function create(array $defaults = array())
    {
        // create a new page for this routing
        $class = $this->getClass();

        $page = new $class;

        if (isset($defaults['routeName']) && isset($this->pageDefaults[$defaults['routeName']])) {
            $defaults = array_merge($this->pageDefaults[$defaults['routeName']], $defaults);
        } else {
            $defaults = array_merge($this->defaults, $defaults);
        }

        foreach ($defaults as $key => $value) {
            $method = 'set' . ucfirst($key);
            $page->$method($value);
        }

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function fixUrl(PageInterface $page)
    {
        if ($page->isInternal()) {
            $page->setUrl(null); // internal routes do not have any url ...

            return;
        }

        // hybrid page cannot be altered
        if (!$page->isHybrid()) {
            // make sure Page has a valid url
            if ($page->getParent()) {
                if (!$page->getSlug()) {
                    $page->setSlug(Page::slugify($page->getName()));
                }

                if ($page->getParent()->getUrl() == '/') {
                    $base = '/';
                } elseif (substr($page->getParent()->getUrl(), -1) != '/') {
                    $base = $page->getParent()->getUrl().'/';
                } else {
                    $base = $page->getParent()->getUrl();
                }

                $page->setUrl($base.$page->getSlug()) ;
            } else {
                // a parent page does not have any slug - can have a custom url ...
                $page->setSlug(null);
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
    public function save($page, $andFlush = true)
    {
        if (!$page->isHybrid()) {
            $this->fixUrl($page);
        }

        parent::save($page, $andFlush);

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function loadPages(SiteInterface $site)
    {
        $pages = $this->getEntityManager()
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
        return $this->getEntityManager()->createQueryBuilder()
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
}
