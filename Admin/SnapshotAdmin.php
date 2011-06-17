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
use Knplabs\Bundle\MenuBundle\MenuItem;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\AdminBundle\Form\FormMapper;

class SnapshotAdmin extends Admin
{
    protected $parentAssociationMapping = 'page';

    protected $list = array(
        'slug' => array('identifier' => true),
        'enabled',
        'publicationDateStart',
        'publicationDateEnd'
    );

    protected $filter = array(
        'routeName',
    );


    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('routeName')
            ->add('enabled', array('required' => false))
            ->add('decorate')
            ->add('slug', array(), array('type' => 'string'))
            ->add('customUrl', array('required' => false), array('type' => 'string'))
            ->add('publicationDateStart')
            ->add('publicationDateEnd')
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        $actions['toggle_enabled'] = $this->trans('toggle_enabled');

        return $actions;
    }

    public function postUpdate($object)
    {
        $this->manager->invalidate(new CacheElement(array(
           'page_id' => $object->getPageId()
        )));
    }

}