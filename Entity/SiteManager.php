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

use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;

/**
 * This class manages SiteInterface persistency with the Doctrine ORM
 *
 * @author  Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SiteManager implements SiteManagerInterface
{
    protected $entityManager;

    protected $class;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param string                      $class
     */
    public function __construct(EntityManager $entityManager, $class)
    {
        $this->entityManager = $entityManager;
        $this->class         = $class;
    }

    /**
     * @param SiteInterface $site
     *
     * @return SiteInterface
     */
    public function save(SiteInterface $site)
    {
        $this->entityManager->persist($site);
        $this->entityManager->flush();

        return $site;
    }

    protected function getRepository()
    {
        return $this->entityManager->getRepository($this->class);
    }

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria = array())
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * @param array $criteria
     *
     * @return SiteInterface
     */
    public function findOneBy(array $criteria = array())
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return SiteInterface
     */
    public function create()
    {
        $class = $this->getClass();

        return new $class;
    }
}