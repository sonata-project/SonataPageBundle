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

use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageInterface;

use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\BlockBundle\Model\BlockInterface;

use Symfony\Bundle\DoctrineBundle\Registry;

class BlockInteractor implements BlockInteractorInterface
{
    protected $pageBlocksLoaded = array();

    protected $registry;

    protected $blockManager;

    /**
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $registry
     * @param \Sonata\BlockBundle\Model\BlockManagerInterface $blockManager
     */
    public function __construct(Registry $registry, BlockManagerInterface $blockManager)
    {
        $this->blockManager = $blockManager;
        $this->registry     = $registry;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->registry->getEntityManagerForClass($this->blockManager->getClass());
    }

    /**
     * {@inheritdoc}
     */
    public function getBlock($id)
    {
        $blocks = $this->getEntityManager()->createQueryBuilder()
            ->select('b')
            ->from($this->blockManager->getClass(), 'b')
            ->where('b.id = :id')
            ->setParameters(array(
              'id' => $id
            ))
            ->getQuery()
            ->execute();

        return count($blocks) > 0 ? $blocks[0] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlocksById(PageInterface $page)
    {
        $blocks = $this->getEntityManager()
            ->createQuery(sprintf('SELECT b FROM %s b INDEX BY b.id WHERE b.page = :page ORDER BY b.position ASC', $this->blockManager->getClass()))
            ->setParameters(array(
                 'page' => $page->getId()
            ))
            ->execute();

        return $blocks;
    }

    /**
     * {@inheritdoc}
     */
    public function saveBlocksPosition(array $data = array())
    {
        $this->getEntityManager()->getConnection()->beginTransaction();

        try {
            foreach ($data as $code => $block) {
                $parent_id = (int) substr($code, 10);

                $block['child'] = (isset($block['child']) && is_array($block['child'])) ? $block['child'] : array();

                $this->saveNestedPosition($block['child'], $parent_id);
            }

        } catch (\Exception $e) {
            $this->getEntityManager()->getConnection()->rollback();

            return false;
        }

         $this->getEntityManager()->getConnection()->commit();

         return true;
    }

    /**
     * @param $blocks
     * @param $parentId
     * @return
     */
    protected function saveNestedPosition($blocks, $parentId)
    {
        if (!is_array($blocks)) {
            return;
        }

        $tableName = $this->getEntityManager()->getClassMetadata($this->blockManager->getClass())->table['name'];

        $position = 1;
        foreach ($blocks as $code => $block) {
            $blockId = (int) substr($code, 10);

            $sql = sprintf('UPDATE %s child, (SELECT p.page_id as page_id FROM %s p WHERE id = %d ) as parent SET child.position = %d, child.parent_id = %d, child.page_id = parent.page_id WHERE child.id = %d',
                $tableName,
                $tableName,
                $parentId,
                $position,
                $parentId,
                $blockId
            );

            $this->getEntityManager()->getConnection()->exec($sql);

            $block['child'] = (isset($block['child']) && is_array($block['child'])) ? $block['child'] : array();

            $this->saveNestedPosition($block['child'], $blockId, $this->getEntityManager());

            $position++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createNewContainer(array $values = array())
    {
        $container = $this->blockManager->create();
        $container->setEnabled(isset($values['enabled']) ? $values['enabled'] : true);
        $container->setCreatedAt(new \DateTime);
        $container->setUpdatedAt(new \DateTime);
        $container->setType('sonata.page.block.container');

        if (isset($values['page'])) {
            $container->setPage($values['page']);
        }

        $container->setSettings(array('name' => isset($values['name']) ? $values['name'] : 'no name defined'));
        $container->setPosition(isset($values['position']) ? $values['position'] : 1);

        if (isset($values['parent'])) {
            $container->setParent($values['parent']);
        }

        $this->blockManager->save($container);

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    public function loadPageBlocks(PageInterface $page)
    {
        if (isset($this->pageBlocksLoaded[$page->getId()])) {
            return array();
        }

        $blocks = $this->getBlocksById($page);

        $page->disableBlockLazyLoading();

        foreach ($blocks as $block) {
            $parent = $block->getParent();

            $block->disableChildrenLazyLoading();
            if (!$parent) {
                $page->addBlocks($block);

                continue;
            }

            $blocks[$block->getParent()->getId()]->disableChildrenLazyLoading();
            $blocks[$block->getParent()->getId()]->addChildren($block);
        }

        $this->pageBlocksLoaded[$page->getId()] = true;

        return $blocks;
    }
}
