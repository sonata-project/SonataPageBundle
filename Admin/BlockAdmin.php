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
    
    protected $form = array(
        'page' => array('edit' => 'list'),
        'enabled',
        'type',
        'children'
    );

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
        $block = $formMapper->getForm()->getData();

        $service = $this->manager->getBlockService($block);

        if ($block->getId() > 0) {
            $service->buildEditForm($formMapper, $block);
        } else {
            $service->buildCreateForm($formMapper, $block);
        }
    }
}