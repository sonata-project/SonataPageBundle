<?php

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
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateTimePickerType;
use Sonata\PageBundle\Route\RoutePageGenerator;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Admin definition for the Site class.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SiteAdmin extends AbstractAdmin
{
    /**
     * @var RoutePageGenerator
     */
    protected $routePageGenerator;

    /**
     * @param string             $code               A Sonata admin code
     * @param string             $class              A Sonata admin class name
     * @param string             $baseControllerName A Sonata admin base controller name
     * @param RoutePageGenerator $routePageGenerator Sonata route page generator service
     */
    public function __construct($code, $class, $baseControllerName, RoutePageGenerator $routePageGenerator)
    {
        $this->routePageGenerator = $routePageGenerator;

        parent::__construct($code, $class, $baseControllerName);
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        $this->routePageGenerator->update($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
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
            ->add('metaKeywords')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('isDefault')
            ->add('enabled', null, ['editable' => true])
            ->add('host')
            ->add('relativePath')
            ->add('locale')
            ->add('enabledFrom')
            ->add('enabledTo')
            ->add('create_snapshots', 'string', ['template' => 'SonataPageBundle:SiteAdmin:list_create_snapshots.html.twig'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('form_site.label_general', ['class' => 'col-md-6'])
                ->add('name')
                ->add('isDefault', null, ['required' => false])
                ->add('enabled', null, ['required' => false])
                ->add('host')
                ->add('locale', LocaleType::class, ['required' => false])
                ->add('relativePath', null, ['required' => false])
                ->add('enabledFrom', DateTimePickerType::class, ['dp_side_by_side' => true])
                ->add('enabledTo', DateTimePickerType::class, ['required' => false, 'dp_side_by_side' => true]
                )
            ->end()
            ->with('form_site.label_seo', ['class' => 'col-md-6'])
                ->add('title', null, ['required' => false])
                ->add('metaDescription', TextareaType::class, ['required' => false])
                ->add('metaKeywords', TextareaType::class, ['required' => false])
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('snapshots', $this->getRouterIdParameter().'/snapshots');
    }
}
