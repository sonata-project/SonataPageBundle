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

use Cocur\Slugify\SlugifyInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;

/**
 * @extends BaseEntityManager<PageInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class PageManager extends BaseEntityManager implements PageManagerInterface
{
    private SlugifyInterface $slugify;

    /**
     * @var array<string, mixed>
     */
    private array $pageDefaults;

    /**
     * @var array<string, mixed>
     */
    private array $defaults;

    /**
     * @param array<string, mixed> $defaults
     * @param array<string, mixed> $pageDefaults
     */
    public function __construct(
        string $class,
        ManagerRegistry $registry,
        SlugifyInterface $slugify,
        array $defaults = [],
        array $pageDefaults = []
    ) {
        parent::__construct($class, $registry);

        $this->slugify = $slugify;
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

    public function createWithDefaults(array $defaults = []): PageInterface
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
            $parent = $page->getParent();

            if (null !== $parent) {
                if (!$page->getSlug()) {
                    $page->setSlug($this->slugify->slugify($page->getName() ?? ''));
                }

                $parentUrl = $parent->getUrl();

                if ('/' === $parentUrl) {
                    $base = '/';
                } elseif ('/' !== substr($parentUrl ?? '', -1)) {
                    $base = $parentUrl.'/';
                } else {
                    $base = $parentUrl;
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

    /**
     * TODO: Add tyephinting once sonata-project/doctrine-extensions < 2 is dropped.
     */
    public function save($entity, $andFlush = true): void
    {
        if (!$entity->isHybrid()) {
            $this->fixUrl($entity);
        }

        parent::save($entity, $andFlush);
    }

    public function loadPages(SiteInterface $site)
    {
        $pages = $this->getEntityManager()
            ->createQuery(sprintf('SELECT p FROM %s p INDEX BY p.id WHERE p.site = %d ORDER BY p.position ASC', $this->class, $site->getId()))
            ->execute();

        foreach ($pages as $page) {
            $parent = $page->getParent();

            if (!$parent) {
                continue;
            }

            $pages[$parent->getId()]->addChild($page);
        }

        return $pages;
    }

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
