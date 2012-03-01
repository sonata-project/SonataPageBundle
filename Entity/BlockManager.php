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

use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\BlockBundle\Model\BlockInterface;

use Sonata\PageBundle\Model\PageInterface;

use Doctrine\ORM\EntityManager;

class BlockManager implements BlockManagerInterface
{
    protected $entityManager;

    protected $class;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param $class
     */
    public function __construct(EntityManager $entityManager, $class)
    {
        $this->entityManager = $entityManager;
        $this->class         = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function save(BlockInterface $page)
    {
        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(BlockInterface $block)
    {
        $this->entityManager->remove($block);
        $this->entityManager->flush();
    }

    protected function getRepository()
    {
        return $this->entityManager->getRepository($this->class);
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
}
