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

use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\BlockInterface;
use Doctrine\ORM\EntityManager;

class BlockManager implements BlockManagerInterface
{
    protected $entityManager;

    protected $class;

    protected $pageBlocksLoaded = array();

    public function __construct(EntityManager $entityManager, $class = 'Application\Sonata\PageBundle\Entity\Block')
    {
        $this->entityManager = $entityManager;
        $this->class         = $class;
    }

    /**
     * Returns a block with the given id
     *
     * @param  $id
     * @return bool
     */
    public function getBlock($id)
    {
        $blocks = $this->entityManager->createQueryBuilder()
            ->select('b')
            ->from($this->class, 'b')
            ->where('b.id = :id')
            ->setParameters(array(
              'id' => $id
            ))
            ->getQuery()
            ->execute();

        return count($blocks) > 0 ? $blocks[0] : false;
    }

    /**
     * Returns a flat list if page's blocks
     *
     * @param PageInterface $page
     * @return
     */
    public function getBlocksById(PageInterface $page)
    {
        $blocks = $this->entityManager
            ->createQuery(sprintf('SELECT b FROM %s b INDEX BY b.id WHERE b.page = :page ORDER BY b.position ASC', $this->class))
            ->setParameters(array(
                 'page' => $page->getId()
            ))
            ->execute();

        return $blocks;
    }

    /**
     * save the block
     *
     * @param array $data
     * @return bool
     */
    public function saveBlocksPosition(array $data = array())
    {
        $this->entityManager->getConnection()->beginTransaction();

        try {
            foreach ($data as $code => $block) {
                $parent_id = (int) substr($code, 10);

                $block['child'] = (isset($block['child']) && is_array($block['child'])) ? $block['child'] : array();

                $this->saveNestedPosition($block['child'], $parent_id);
            }

        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollback();

            return false;
        }

         $this->entityManager->getConnection()->commit();

         return true;
    }

    /**
     * Save block by re attaching a page to the correct page and correct block's parent.
     *
     * @param array $blocks
     * @param integer $parentId
     * @return
     */
    protected function saveNestedPosition($blocks, $parentId)
    {
        if (!is_array($blocks)) {
            return;
        }

        $tableName = $this->entityManager->getClassMetadata($this->class)->table['name'];

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

            $this->entityManager->getConnection()->exec($sql);

            $block['child'] = (isset($block['child']) && is_array($block['child'])) ? $block['child'] : array();

            $this->saveNestedPosition($block['child'], $blockId, $this->entityManager);

            $position++;
        }
    }

    /**
     * @param array $values
     * @return \Sonata\PageBundle\Model\BlockInterface
     */
    public function createNewContainer(array $values = array())
    {
        $container = $this->create();
        $container->setEnabled(isset($values['enabled']) ? $values['enabled'] : true);
        $container->setCreatedAt(new \DateTime);
        $container->setUpdatedAt(new \DateTime);
        $container->setType('sonata.page.block.container');
        $container->setPage(isset($values['page']) ? $values['page'] : true);
        $container->setSettings(array('name' => isset($values['name']) ? $values['name'] : 'no name defined'));
        $container->setPosition(isset($values['position']) ? $values['position'] : 1);

        return $container;
    }

    /**
     * load blocks attached the given page
     *
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return array $blocks
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

    /**
     * @param \Sonata\PageBundle\Model\BlockInterface $page
     * @return \Sonata\PageBundle\Model\BlockInterface
     */
    public function save(BlockInterface $page)
    {
        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return $page;
    }

     /**
     * @return \Sonata\PageBundle\Model\BlockInterface
     */
    public function create()
    {
        return $this->class;
    }
}