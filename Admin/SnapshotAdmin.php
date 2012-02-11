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
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

use Sonata\CacheBundle\Cache\CacheManagerInterface;

class SnapshotAdmin extends Admin
{
    protected $cacheManager;

    protected $parentAssociationMapping = 'page';

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('url')
            ->add('enabled')
            ->add('publicationDateStart')
            ->add('publicationDateEnd')
        ;
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('routeName');
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('enabled', null, array('required' => false))
            ->add('publicationDateStart')
            ->add('publicationDateEnd')
//            ->add('content')
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        $actions['toggle_enabled'] = array(
            'label' => $this->trans('toggle_enabled'),
            'ask_confirmation' => true
        );

        return $actions;
    }

    public function postUpdate($object)
    {
        $this->cacheManager->invalidate(array(
           'page_id' => $object->getPage()->getId()
        ));
    }

    public function postPersist($object)
    {
        $this->cacheManager->invalidate(array(
           'page_id' => $object->getPage()->getId()
        ));
    }

    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }
}