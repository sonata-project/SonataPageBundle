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
use Sonata\PageBundle\Page\Manager;

class BlockAdmin extends Admin
{
    protected $parentAssociationMapping = 'page';

    protected $manager;

    protected $filter = array(
//        'page',
        'enabled',
        'type',
    );

    protected $list = array(
        'id' => array('identifier' => true),
        'page',
        'enabled',
        'type',
    );
    /**
     * @param \Sonata\PageBundle\Page\Manager $manager
     * @return void
     */
    public function setManager(Manager $manager)
    {
        $this->manager = $manager;
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

        // fix temporary bug with twig and form framework
        $formMapper->getFormBuilder()->remove('_token');

//        $formMapper->add('enabled', array('required' => false));

        if ($block) {
            $service = $this->manager->getBlockService($block);

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
            $service = $this->manager->getBlockService($subject);
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
        $service      = $this->manager->getBlockService($object);
        $cacheElement = $service->getCacheElement($object);

        $this->manager->invalidate($cacheElement);
    }

    public function postPersist($object)
    {
        $service      = $this->manager->getBlockService($object);
        $cacheElement = $service->getCacheElement($object);

        $this->manager->invalidate($cacheElement);
    }
}