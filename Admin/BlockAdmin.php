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
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\PageBundle\CmsManager\CmsPageManager;


class BlockAdmin extends Admin
{
    protected $parentAssociationMapping = 'page';

    protected $cmsManager;

    public function setCmsManager(CmsPageManager $cmsManager)
    {
        $this->cmsManager = $cmsManager;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('savePosition', 'save-position');
        $collection->add('view', $this->getRouterIdParameter().'/view');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('type')
            ->add('enabled')
            ->add('updatedAt')
            ->add('position')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('enabled')
            ->add('type')
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $block = $this->getSubject();

        if ($block) {
            $service = $this->cmsManager->getBlockService($block);
            if ($block->getId() > 0) {
                $service->buildEditForm($formMapper, $block);
            } else {
                $service->buildCreateForm($formMapper, $block);
            }
        } else {
            $formMapper
                ->add('page', 'sonata_type_model')
                ->add('parent', 'sonata_type_model')
                ->add('type', 'sonata_page_block_choice')
                ->add('position');
        }
    }

    public function getObject($id)
    {
        $subject = parent::getObject($id);

        if ($subject) {
            $service = $this->cmsManager->getBlockService($subject);
            $subject->setSettings(array_merge($service->getDefaultSettings(), $subject->getSettings()));

            $service->load($subject);
        }

        return $subject;
    }

    public function preUpdate($object)
    {
        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
        $this->cmsManager->getBlockService($object)->preUpdate($object);
    }

    public function postUpdate($object)
    {
        $service      = $this->cmsManager->getBlockService($object);
        $cacheElement = $service->getCacheElement($object);

        $this->cmsManager->invalidate($cacheElement);
    }

    public function prePersist($object)
    {
        $this->cmsManager->getBlockService($object)->prePersist($object);

        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
    }

    public function postPersist($object)
    {
        $service      = $this->cmsManager->getBlockService($object);
        $cacheElement = $service->getCacheElement($object);

        $this->cmsManager->invalidate($cacheElement);
    }
}