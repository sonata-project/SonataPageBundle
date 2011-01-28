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


class BasePageRepository extends \Doctrine\ORM\EntityRepository
{


    /**
     * return a page with the given routeName
     * 
     * @param  $routeName
     * @return Page|false
     */
    public function getPageByName($routeName) {
        
        $pages = $this->_em->createQueryBuilder()
            ->select('p, t')
            ->from('Application\Sonata\PageBundle\Entity\Page', 'p')
            ->where('p.route_name = :route_name')
            ->leftJoin('p.template', 't')
            ->setParameters(array(
                'route_name' => $routeName
            ))
            ->getQuery()
            ->execute();

        return count($pages) > 0 ? $pages[0] : false;
    }

    /**
     * return a page with the give slug
     *
     * @param  $routeName
     * @return bool
     */
    public function getPageBySlug($slug) {

        $pages = $this->_em->createQueryBuilder()
            ->select('p')
            ->from('Application\Sonata\PageBundle\Entity\Page', 'p')
            ->leftJoin('p.template', 't')
            ->where('p.slug = :slug')
            ->setParameters(array(
                'slug' => $slug
            ))
            ->getQuery()
            ->execute();


        return count($pages) > 0 ? $pages[0] : false;
    }

    public function getDefaultTemplate()
    {
        $templates = $this->_em->createQueryBuilder()
            ->select('t')
            ->from('Application\Sonata\PageBundle\Entity\Template', 't')
            ->where('t.id = :id')
            ->setParameters(array(
                 'id' => 1
            ))
            ->getQuery()
            ->execute();

        return count($templates) > 0 ? $templates[0] : false;
    }

    /**
     * return a block with the given id
     * 
     * @param  $id
     * @return bool
     */
    public function getBlock($id)
    {
        $blocks = $this->_em->createQueryBuilder()
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
    public function getBlocksById($page)
    {
        $blocks = $this->_em
            ->createQuery('SELECT b FROM Application\Sonata\PageBundle\Entity\Block b INDEX BY b.id WHERE b.page = :page ORDER BY b.position ASC')
            ->setParameters(array(
                 'page' => $page->getId()
            ))
            ->execute();

        return $blocks;
    }

    /**
     * load blocks attached the given page
     *
     * @param  $page
     * @return array $blocks
     */
    public function loadPageBlocks($page)
    {

        $blocks = $this->getBlocksById($page);
        
        $page->disableBlockLazyLoading();

        foreach($blocks as $block) {

            $parent = $block->getParent();

            $block->disableChildrenLazyLoading();
            if(!$parent) {
                $page->addBlocks($block);

                continue;
            }

            $blocks[$block->getParent()->getId()]->disableChildrenLazyLoading();
            $blocks[$block->getParent()->getId()]->addChildren($block);
        }

        return $blocks;
    }

    public function createNewPage(array $defaults)
    {
        // create a new page for this routing

        $page = new $this->_class->name;
        $page->setTemplate(isset($defaults['template']) ? $defaults['template'] : null);
        $page->setEnabled(isset($defaults['enabled']) ? $defaults['enabled'] : true);
        $page->setRouteName(isset($defaults['routeName']) ? $defaults['routeName'] : null);
        $page->setName(isset($defaults['name']) ? $defaults['name'] : null);
        $page->setLoginRequired(isset($defaults['loginRequired']) ? $defaults['loginRequired'] : null);
        $page->setCreatedAt(new \DateTime);
        $page->setUpdatedAt(new \DateTime);

        return $page;
    }

    /**
     * save the block
     *
     * @param array $data
     * @return bool
     */
    public function saveBlocksPosition(array $data = array())
    {
        $this->_em->getConnection()->beginTransaction();

        try {
            foreach($data as $code => $block) {

                $parent_id = (int) substr($code, 10);

                $block['child'] = (isset($block['child']) && is_array($block['child'])) ? $block['child'] : array();

                $this->saveNestedPosition($block['child'], $parent_id);
            }

        } catch (\Exception $e) {
            $this->_em->getConnection()->rollback();

            return false;
        }

         $this->_em->getConnection()->commit();

         return true;
    }

    /**
     * Save block by re attaching a page to the correct page and correct block's parent.
     *
     * @param  $blocks
     * @param  $parent_id
     * @param  $entity_manager
     * @return
     */
    protected function saveNestedPosition($blocks, $parent_id)
    {

        if(!is_array($blocks)) {
            return;
        }

        $table_name = $this->_em->getClassMetadata('Application\Sonata\PageBundle\Entity\Block')->table['name'];

        $position = 1;
        foreach($blocks as $code => $block) {
            $block_id = (int) substr($code, 10);

            $sql = sprintf('UPDATE %s child, (SELECT p.page_id as page_id FROM %s p WHERE id = %d ) as parent SET child.position = %d, child.parent_id = %d, child.page_id = parent.page_id WHERE child.id = %d',
                $table_name,
                $table_name,
                $parent_id,
                $position,
                $parent_id,
                $block_id
            );

            $this->_em->getConnection()->exec($sql);

            $block['child'] = (isset($block['child']) && is_array($block['child'])) ? $block['child'] : array();

            $this->saveNestedPosition($block['child'], $block_id, $this->_em);

            $position++;
        }
    }

    public function createNewContainer(array $values)
    {

        $container = new \Application\Sonata\PageBundle\Entity\Block;
        $container->setEnabled(isset($values['enabled']) ? $values['enabled'] : true);
        $container->setCreatedAt(new \DateTime);
        $container->setUpdatedAt(new \DateTime);
        $container->setType('core.container');
        $container->setPage(isset($values['page']) ? $values['page'] : true);
        $container->setSettings(array('name' => isset($values['name']) ? $values['name'] : 'no name defined'));
        $container->setPosition(isset($values['position']) ? $values['position'] : 1);

        return $container;
    }
    
    public function save($object)
    {
        $this->_em->persist($object);
        $this->_em->flush();

        return $object;
    }
}