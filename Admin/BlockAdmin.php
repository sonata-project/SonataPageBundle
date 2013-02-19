<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

use Sonata\CacheBundle\Cache\CacheManagerInterface;

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

/**
 * Admin class for the Block model
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class BlockAdmin extends Admin
{
    protected $parentAssociationMapping = 'page';

    protected $blockManager;

    protected $cacheManager;

    protected $inValidate = false;

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('savePosition', 'save-position');
        $collection->add('view', $this->getRouterIdParameter().'/view');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('type')
            ->add('name')
            ->add('enabled')
            ->add('updatedAt')
            ->add('position')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled')
            ->add('type')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $block = $this->getSubject();

        // add name on all forms
        $formMapper->add('name');

        $isContainerRoot = $block && $block->getType() == 'sonata.page.block.container' && !$this->hasParentFieldDescription();
        $isStandardBlock = $block && $block->getType() != 'sonata.page.block.container' && !$this->hasParentFieldDescription();

        if ($isContainerRoot || $isStandardBlock) {
            $service = $this->blockManager->get($block);

            if ($block->getId() > 0) {
                $service->buildEditForm($formMapper, $block);
            } else {
                $service->buildCreateForm($formMapper, $block);
            }
        } else {

            $formMapper
                ->add('type', 'sonata_block_service_choice', array(
                    'context' => 'cms'
                ))
                ->add('enabled')
                ->add('position');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getObject($id)
    {
        $subject = parent::getObject($id);

        if ($subject) {
            $service = $this->blockManager->get($subject);
            $subject->setSettings(array_merge($service->getDefaultSettings(), $subject->getSettings()));

            $service->load($subject);
        }

        return $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
        $object->getPage()->setEdited(true);

        $this->blockManager->get($object)->preUpdate($object);
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        $service = $this->blockManager->get($object);

        $this->cacheManager->invalidate($service->getCacheKeys($object));
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        $this->blockManager->get($object)->prePersist($object);

        $object->getPage()->setEdited(true);

        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        $service = $this->blockManager->get($object);

        $this->cacheManager->invalidate($service->getCacheKeys($object));
    }

    /**
     * @param \Sonata\BlockBundle\Block\BlockServiceManagerInterface $blockManager
     */
    public function setBlockManager(BlockServiceManagerInterface $blockManager)
    {
        $this->blockManager = $blockManager;
    }

    /**
     * @param \Sonata\CacheBundle\Cache\CacheManagerInterface $cacheManager
     */
    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }
}
