<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Cache\CacheManagerInterface;
use Sonata\Form\Type\DateTimePickerType;

/**
 * Admin definition for the Snapshot class.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SnapshotAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Snapshot';

    /**
     * @var CacheManagerInterface
     */
    protected $cacheManager;

    protected $accessMapping = [
        'batchToggleEnabled' => 'EDIT',
    ];

    public function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('url')
            ->add('enabled')
            ->add('publicationDateStart')
            ->add('publicationDateEnd')
        ;
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('routeName');
    }

    public function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('enabled', null, ['required' => false])
            ->add('publicationDateStart', DateTimePickerType::class, ['dp_side_by_side' => true])
            ->add('publicationDateEnd', DateTimePickerType::class, ['required' => false, 'dp_side_by_side' => true])
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        $actions['toggle_enabled'] = [
            'label' => $this->trans('toggle_enabled'),
            'ask_confirmation' => true,
        ];

        return $actions;
    }

    public function postUpdate($object): void
    {
        $this->cacheManager->invalidate([
            'page_id' => $object->getPage()->getId(),
        ]);
    }

    public function postPersist($object): void
    {
        $this->cacheManager->invalidate([
            'page_id' => $object->getPage()->getId(),
        ]);
    }

    public function setCacheManager(CacheManagerInterface $cacheManager): void
    {
        $this->cacheManager = $cacheManager;
    }
}
