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
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Twig\CanonicalizeRuntime;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Route\RoutePageGenerator;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * @extends AbstractAdmin<SiteInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SiteAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Site';

    public function __construct(private RoutePageGenerator $routePageGenerator)
    {
        parent::__construct();
    }

    protected function postPersist(object $object): void
    {
        $this->routePageGenerator->update($object);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('name')
            ->add('isDefault')
            ->add('enabled')
            ->add('host')
            ->add('locale')
            ->add('relativePath')
            ->add('enabledFrom')
            ->add('enabledTo')
            ->add('title')
            ->add('metaDescription')
            ->add('metaKeywords');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('isDefault')
            ->add('enabled', null, ['editable' => true])
            ->add('host')
            ->add('relativePath')
            ->add('locale')
            ->add('enabledFrom')
            ->add('enabledTo')
            ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                'translation_domain' => 'SonataAdminBundle',
                'actions' => [
                    'create_snapshot' => [
                        'template' => '@SonataPage/SiteAdmin/list_action_create_snapshots.html.twig',
                    ],
                    'edit' => [],
                ],
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        // TODO: Keep else when dropping support for `sonata-project/form-extensions` 1.x
        $datepickerOptions = class_exists(CanonicalizeRuntime::class) ?
            ['dp_side_by_side' => true] :
            ['datepicker_options' => ['display' => ['sideBySide' => true]]];

        $form
            ->with('general', ['class' => 'col-md-6'])
                ->add('name')
                ->add('isDefault', null, ['required' => false])
                ->add('enabled', null, ['required' => false])
                ->add('host')
                ->add('locale', LocaleType::class, ['required' => false])
                ->add('relativePath', null, ['required' => false])
                ->add('enabledFrom', DateTimePickerType::class, $datepickerOptions)
                ->add(
                    'enabledTo',
                    DateTimePickerType::class,
                    ['required' => false] + $datepickerOptions
                )
            ->end()
            ->with('seo', ['class' => 'col-md-6'])
                ->add('title', null, ['required' => false])
                ->add('metaDescription', TextareaType::class, ['required' => false])
                ->add('metaKeywords', TextareaType::class, ['required' => false])
            ->end();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->add('snapshots', $this->getRouterIdParameter().'/snapshots');
    }
}
