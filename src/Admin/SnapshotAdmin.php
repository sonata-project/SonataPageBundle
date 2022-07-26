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
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\PageBundle\Model\SnapshotInterface;

/**
 * @extends AbstractAdmin<SnapshotInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SnapshotAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Snapshot';

    protected function getAccessMapping(): array
    {
        return [
            'batchToggleEnabled' => AdminPermissionMap::PERMISSION_EDIT,
        ];
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('url')
            ->add('enabled')
            ->add('publicationDateStart')
            ->add('publicationDateEnd');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('routeName');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('enabled', null, ['required' => false])
            ->add('publicationDateStart', DateTimePickerType::class, ['dp_side_by_side' => true])
            ->add('publicationDateEnd', DateTimePickerType::class, ['required' => false, 'dp_side_by_side' => true]);
    }

    protected function configureBatchActions(array $actions): array
    {
        $actions = parent::configureBatchActions($actions);

        $actions['toggle_enabled'] = [
            'label' => 'toggle_enabled',
            'ask_confirmation' => true,
        ];

        return $actions;
    }
}
