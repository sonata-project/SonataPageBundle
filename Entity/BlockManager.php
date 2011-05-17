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

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * return a block with the given id
     *
     * @param  $id
     * @return bool
     */
    public function getBlock($id)
    {
        $blocks = $this->entityManager->createQueryBuilder()
            ->select('b')
            ->from('Application\Sonata\PageBundle\Entity\Block', 'b')
            ->where('b.id = :id')
            ->setParameters(array(
              'id' => $id
            ))
            ->getQuery()
            ->execute();

        return count($blocks) > 0 ? $blocks[0] : false;
    }

    /**
     *
     * return a flat list if page's blocks
     *
     * @param  $page
     * @return
     */
    public function getBlocksById(PageInterface $page)
    {
        $blocks = $this->entityManager
            ->createQuery('SELECT b FROM Application\Sonata\PageBundle\Entity\Block b INDEX BY b.id WHERE b.page = :page ORDER BY b.position ASC')
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
     * @param  $blocks
     * @param  $parentId
     * @return
     */
    protected function saveNestedPosition($blocks, $parentId)
    {

        if (!is_array($blocks)) {
            return;
        }

        $tableName = $this->entityManager->getClassMetadata('Application\Sonata\PageBundle\Entity\Block')->table['name'];

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

    public function createNewContainer(array $values = array())
    {
        $container = new \Application\Sonata\PageBundle\Entity\Block;
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
     * @param  $page
     * @return array $blocks
     */
    public function loadPageBlocks(PageInterface $page)
    {
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
}