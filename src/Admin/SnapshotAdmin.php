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
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\Form\Twig\CanonicalizeRuntime;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * @extends AbstractAdmin<SnapshotInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SnapshotAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Snapshot';

    public function __construct(
        private TransformerInterface $transformer,
        private PageManagerInterface $pageManager,
        private SnapshotManagerInterface $snapshotManager
    ) {
        parent::__construct();
    }

    protected function getAccessMapping(): array
    {
        return [
            'batchToggleEnabled' => AdminPermissionMap::PERMISSION_EDIT,
        ];
    }

    protected function alterNewInstance(object $object): void
    {
        if (!$this->hasRequest()) {
            return;
        }

        $pageId = $this->getRequest()->query->get('pageId');

        if (null === $pageId) {
            return;
        }

        $object->setPage($this->pageManager->find($pageId));
    }

    protected function prePersist(object $object): void
    {
        $page = $this->isChild() ? $this->getParent()->getSubject() : $object->getPage();

        if (null !== $page) {
            \assert($page instanceof PageInterface);
            $this->transformer->create($page, $object);
        }
    }

    protected function postPersist(object $object): void
    {
        $page = $object->getPage();

        if (null !== $page) {
            $page->setEdited(false);
            $this->pageManager->save($page);
        }

        $this->snapshotManager->enableSnapshots([$object]);
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('url', null, ['route' => ['name' => 'edit']])
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
        if ($this->isCurrentRoute('create')) {
            $hasPage = $this->hasSubject() && null !== $this->getSubject()->getPage();

            $form->add('page', $hasPage ? ModelHiddenType::class : null);
        } else {
            // TODO: Keep else when dropping support for `sonata-project/form-extensions` 1.x
            $datepickerOptions = class_exists(CanonicalizeRuntime::class) ?
                ['dp_side_by_side' => true] :
                ['datepicker_options' => ['display' => ['sideBySide' => true]]];

            $form
                ->add('enabled', null, ['required' => false])
                ->add('publicationDateStart', DateTimePickerType::class, $datepickerOptions)
                ->add('publicationDateEnd', DateTimePickerType::class, ['required' => false] + $datepickerOptions);
        }
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
