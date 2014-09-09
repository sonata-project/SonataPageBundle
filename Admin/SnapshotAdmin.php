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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

use Sonata\Cache\CacheManagerInterface;

/**
 * Admin definition for the Snapshot class
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SnapshotAdmin extends Admin
{
    protected $cacheManager;

    protected $parentAssociationMapping = 'page';

    /**
     * {@inheritdoc}
     */
    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('url')
            ->add('enabled')
            ->add('publicationDateStart')
            ->add('publicationDateEnd')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('routeName');
    }

    /**
     * {@inheritdoc}
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('enabled', null, array('required' => false))
            ->add('publicationDateStart', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
            ->add('publicationDateEnd', 'sonata_type_datetime_picker', array('required' => false, 'dp_side_by_side' => true))
//            ->add('content')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        $actions['toggle_enabled'] = array(
            'label'            => $this->trans('toggle_enabled'),
            'ask_confirmation' => true
        );

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        $this->cacheManager->invalidate(array(
            'page_id' => $object->getPage()->getId()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        $this->cacheManager->invalidate(array(
            'page_id' => $object->getPage()->getId()
        ));
    }

    /**
     * @param \Sonata\Cache\CacheManagerInterface $cacheManager
     */
    public function setCacheManager(CacheManagerInterface $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }
}
