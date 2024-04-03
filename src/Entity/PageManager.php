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
    /**
     * @param class-string<PageInterface> $class
     * @param array<string, mixed>        $defaults
     * @param array<string, mixed>        $pageDefaults
     */
    public function __construct(
        string $class,
        ManagerRegistry $registry,
        private SlugifyInterface $slugify,
        private array $defaults = [],
        private array $pageDefaults = []
    ) {
        parent::__construct($class, $registry);
    }

    public function getPageByUrl(SiteInterface $site, string $url): ?PageInterface
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
                $slug = $page->getSlug();

                if (null === $slug) {
                    $slug = $this->slugify->slugify($page->getName() ?? '');

                    $page->setSlug($slug);
                }

                $parentUrl = $parent->getUrl();

                if ('/' === $parentUrl) {
                    $base = '/';
                } elseif (!str_ends_with($parentUrl ?? '', '/')) {
                    $base = $parentUrl.'/';
                } else {
                    $base = $parentUrl;
                }

                $url = $page->getCustomUrl() ?? $slug;
                $page->setUrl('/'.ltrim($base.$url, '/'));
            } else {
                $page->setSlug(null);

                $url = $page->getCustomUrl() ?? '';
                $page->setUrl('/'.ltrim($url, '/'));
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

    public function loadPages(SiteInterface $site): array
    {
        $siteId = $site->getId();
        \assert(null !== $siteId);

        /** @var array<PageInterface> */
        $pages = $this->getEntityManager()
            ->createQuery(sprintf('SELECT p FROM %s p INDEX BY p.id WHERE p.site = %d ORDER BY p.position ASC', $this->class, $siteId))
            ->execute();

        foreach ($pages as $page) {
            $parent = $page->getParent();

            if (null === $parent) {
                continue;
            }

            $parentId = $parent->getId();
            \assert(null !== $parentId);

            $pages[$parentId]->addChild($page);
        }

        return $pages;
    }

    public function getHybridPages(SiteInterface $site): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from($this->class, 'p')
            ->where('p.routeName <> :routeName and p.site = :site')
            ->setParameter('routeName', PageInterface::PAGE_ROUTE_CMS_NAME)
            ->setParameter('site', $site->getId())
            ->getQuery()
            ->execute();
    }
}
