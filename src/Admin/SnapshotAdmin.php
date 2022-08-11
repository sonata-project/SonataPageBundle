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
use Sonata\AdminBundle\Security\Acl\Permission\AdminPermissionMap;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * @extends AbstractAdmin<SnapshotInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SnapshotAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Snapshot';

    private TransformerInterface $transformer;

    private PageManagerInterface $pageManager;

    public function __construct(
        TransformerInterface $transformer,
        PageManagerInterface $pageManager
    ) {
        parent::__construct();

        $this->transformer = $transformer;
        $this->pageManager = $pageManager;
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
        \assert($page instanceof PageInterface);

        $snapshot = $this->transformer->create($page);

        $object->setPage($snapshot->getPage());
        $object->setUrl($snapshot->getUrl());
        $object->setEnabled($snapshot->getEnabled());
        $object->setRouteName($snapshot->getRouteName());
        $object->setPageAlias($snapshot->getPageAlias());
        $object->setType($snapshot->getType());
        $object->setName($snapshot->getName());
        $object->setPosition($snapshot->getPosition());
        $object->setDecorate($snapshot->getDecorate());
        $object->setSite($snapshot->getSite());
        $object->setParentId($snapshot->getParentId());
        $object->setContent($snapshot->getContent());
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
        if ($this->isCurrentRoute('create')) {
            $hasPage = $this->hasSubject() && null !== $this->getSubject()->getPage();

            $form->add('page', $hasPage ? ModelHiddenType::class : null);
        } else {
            $form
                ->add('enabled', null, ['required' => false])
                ->add('publicationDateStart', DateTimePickerType::class, ['dp_side_by_side' => true])
                ->add('publicationDateEnd', DateTimePickerType::class, ['required' => false, 'dp_side_by_side' => true]);
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
