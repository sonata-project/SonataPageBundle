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
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\Template;
use Sonata\PageBundle\Model\SiteInterface;

use Sonata\PageBundle\Model\Page;
use Doctrine\ORM\EntityManager;

class PageManager implements PageManagerInterface
{
    protected $entityManager;

    protected $class;

    protected $templates = array();

    protected $defaultTemplateCode = 'default';

    public function __construct(EntityManager $entityManager, $class = 'Application\Sonata\PageBundle\Entity\Page', $templates = array())
    {
        $this->entityManager = $entityManager;
        $this->class         = $class;
        $this->templates     = $templates;
    }

    /**
     * return a page with the given routeName
     *
     * @param string $routeName
     * @return PageInterface|false
     */
    public function getPageByName(SiteInterface $site, $routeName)
    {
        return $this->findOneBy(array('routeName' => $routeName, 'site' => $site->getId()));
    }

    protected function getRepository()
    {
        return $this->entityManager->getRepository($this->class);
    }

    /**
     * return a page with the give slug
     *
     * @param string $url
     * @return PageInterface
     */
    public function getPageByUrl(SiteInterface $site, $url)
    {
        return $this->findOneBy(array('url' => $url, 'site' => $site->getId()));
    }

    public function getDefaultTemplateCode()
    {
        return $this->defaultTemplateCode;
    }

    public function setDefaultTemplateCode($code)
    {
        $this->defaultTemplateCode = $code;

        return $this;
    }

    protected function getCreateNewPageDefaults()
    {
        $time = new \DateTime;

        return array(
            'templateCode'  => $this->getDefaultTemplateCode(),
            'enabled'       => true,
            'routeName'     => null,
            'name'          => null,
            'slug'          => null,
            'url'           => null,
            'requestMethod' => null,
            'decorate'      => true,
            'createdAt'     => $time,
            'updatedAt'     => $time,
        );
    }

    public function createNewPage(array $defaults = array())
    {
        // create a new page for this routing
        $page = $this->getNewInstance();
        foreach ($this->getCreateNewPageDefaults() as $key => $value) {
            if (isset($defaults[$key])) {
                $value = $defaults[$key];
            }
            $method = 'set' . ucfirst($key);
            $page->$method($value);
        }

        return $page;
    }

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
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getNewInstance()
    {
        $class = $this->getClass();

        return new $class;
    }

    public function findBy(array $criteria = array())
    {
        return $this->getRepository()->findBy($criteria);
    }

    public function findOneBy(array $criteria = array())
    {
        return $this->getRepository()->findOneBy($criteria);
    }

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
     * @param $templates
     * @return void
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
     * @throws \RunTimeException
     * @param $code
     * @return string
     */
    public function getTemplate($code)
    {
        if (!isset($this->templates[$code])) {
            throw new \RunTimeException(sprintf('No template references whith the code : %s', $code));
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