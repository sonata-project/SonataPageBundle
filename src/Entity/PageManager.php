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
use Sonata\PageBundle\Service\Contract\PageFixerInterface;
use Sonata\PageBundle\Service\PageFixerService;
use Symfony\Component\String\Slugger\AsciiSlugger;

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
        private SlugifyInterface|PageFixerInterface $pageFixer,
        private array $defaults = [],
        private array $pageDefaults = [],
    ) {
        // NEXT_MAJOR: Remove the if block bellow and "cocur/slugify" dependecy.
        if ($this->pageFixer instanceof SlugifyInterface) {
            @trigger_error(\sprintf(
                'Inject %s in %s is deprecated since version 4.10.0 and will be removed in 5.0, use %s instead of.',
                SlugifyInterface::class,
                self::class,
                PageFixerInterface::class,
            ), \E_USER_DEPRECATED);
        }
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

    /**
     * NEXT_MAJOR: keep only $this->fixPageUrl->fixUrl$page) in this method.
     */
    public function fixUrl(PageInterface $page): void
    {
        $pageFixer = $this->pageFixer;

        if ($pageFixer instanceof SlugifyInterface) {
            @trigger_error(\sprintf(
                'Inject %s in %s is deprecated since version 4.10.0 and will be removed in 5.0, use %s instead of.',
                SlugifyInterface::class,
                self::class,
                PageFixerInterface::class,
            ), \E_USER_DEPRECATED);
            $pageFixer = new PageFixerService(new AsciiSlugger());
        }

        $pageFixer->fixUrl($page);
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
            ->createQuery(\sprintf('SELECT p FROM %s p INDEX BY p.id WHERE p.site = %s ORDER BY p.position ASC', $this->class, $siteId))
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
