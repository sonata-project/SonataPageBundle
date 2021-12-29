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

use Doctrine\Persistence\ManagerRegistry;
use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\PageBundle\Model\Page;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;

/**
 * This class manages PageInterface persistency with the Doctrine ORM.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.26
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
     * @param string $class
     */
    public function __construct($class, ManagerRegistry $registry, array $defaults = [], array $pageDefaults = [])
    {
        parent::__construct($class, $registry);

        $this->defaults = $defaults;
        $this->pageDefaults = $pageDefaults;
    }

    public function getPageByUrl(SiteInterface $site, $url)
    {
        return $this->findOneBy([
            'url' => $url,
            'site' => $site->getId(),
        ]);
    }

    public function create(array $defaults = [])
    {
        // create a new page for this routing
        $class = $this->getClass();

        $page = new $class();

        if (isset($defaults['routeName'], $this->pageDefaults[$defaults['routeName']])) {
            $defaults = array_merge($this->pageDefaults[$defaults['routeName']], $defaults);
        } else {
            $defaults = array_merge($this->defaults, $defaults);
        }

        foreach ($defaults as $key => $value) {
            $method = 'set'.ucfirst($key);
            $page->$method($value);
        }

        return $page;
    }

    public function fixUrl(PageInterface $page): void
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

                if ('/' === $page->getParent()->getUrl()) {
                    $base = '/';
                } elseif ('/' !== substr($page->getParent()->getUrl(), -1)) {
                    $base = $page->getParent()->getUrl().'/';
                } else {
                    $base = $page->getParent()->getUrl();
                }

                $page->setUrl($base.$page->getSlug());
            } else {
                // a parent page does not have any slug - can have a custom url ...
                $page->setSlug('');
                $page->setUrl('/'.$page->getSlug());
            }
        }

        foreach ($page->getChildren() as $child) {
            $this->fixUrl($child);
        }
    }

    public function save($entity, $andFlush = true)
    {
        if (!$entity->isHybrid()) {
            $this->fixUrl($entity);
        }

        parent::save($entity, $andFlush);

        return $entity;
    }

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
     * @return PageInterface[]
     */
    public function getHybridPages(SiteInterface $site)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from($this->class, 'p')
            ->where('p.routeName <> :routeName and p.site = :site')
            ->setParameters([
                'routeName' => PageInterface::PAGE_ROUTE_CMS_NAME,
                'site' => $site->getId(),
            ])
            ->getQuery()
            ->execute();
    }
}
