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
use Sonata\PageBundle\CmsManager\CmsPageManager;

class BlockAdmin extends Admin
{
    protected $parentAssociationMapping = 'page';

    protected $cmsManager;

    protected $filter = array(
//        'page',
        'enabled',
        'type',
    );

    protected $list = array(
        'type' => array('identifier' => true),
        'enabled',
        'updatedAt',
        'position'
    );
    /**
     * @param \Sonata\PageBundle\CmsManager\CmsPageManager $manager
     * @return void
     */
    public function setCmsManager(CmsPageManager $cmsManager)
    {
        $this->cmsManager = $cmsManager;
    }

    public function configureRoutes(RouteCollection $collection)
    {
        $collection->add('savePosition', 'save-position');
        $collection->add('view', $this->getRouterIdParameter().'/view');
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @return void
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $block = $formMapper->getFormBuilder()->getData();

//        $formMapper->add('enabled', array('required' => false));

        if ($block) {
            $service = $this->cmsManager->getBlockService($block);

            if ($block->getId() > 0) {
                $service->buildEditForm($formMapper, $block);
            } else {
                $service->buildCreateForm($formMapper, $block);
            }
        } else {
            $formMapper
              ->addType('type', 'sonata_page_block_choice', array(), array('type' => 'choice'))
              ->add('position');
        }
    }

    public function getObject($id)
    {
        $subject = parent::getObject($id);

        if ($subject) {
            $service = $this->cmsManager->getBlockService($subject);
            $subject->setSettings(array_merge($service->getDefaultSettings(), $subject->getSettings()));
        }

        return $subject;
    }

    public function preUpdate($object)
    {
        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
    }

    public function preInsert($object)
    {
        // fix weird bug with setter object not being call
        $object->setChildren($object->getChildren());
    }

    public function postUpdate($object)
    {
        $service      = $this->cmsManager->getBlockService($object);
        $cacheElement = $service->getCacheElement($object);

        $this->cmsManager->invalidate($cacheElement);
    }

    public function postPersist($object)
    {
        $service      = $this->cmsManager->getBlockService($object);
        $cacheElement = $service->getCacheElement($object);

        $this->cmsManager->invalidate($cacheElement);
    }
}