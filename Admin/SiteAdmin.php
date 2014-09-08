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

use Sonata\PageBundle\Route\RoutePageGenerator;

/**
 * Admin definition for the Site class
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SiteAdmin extends Admin
{
    /**
     * @var RoutePageGenerator
     */
    protected $routePageGenerator;

    /**
     * Constructor
     *
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
            ->add('enabled', null, array('editable' => true))
            ->add('host')
            ->add('relativePath')
            ->add('locale')
            ->add('enabledFrom')
            ->add('enabledTo')
            ->add('create_snapshots', 'string', array('template' => 'SonataPageBundle:SiteAdmin:list_create_snapshots.html.twig'))
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
            ->with($this->trans('form_site.label_general'), array('class' => 'col-md-6'))
                ->add('name')
                ->add('isDefault', null, array('required' => false))
                ->add('enabled', null, array('required' => false))
                ->add('host')
                ->add('locale', 'locale', array(
                    'required' => false
                ))
                ->add('relativePath', null, array('required' => false))
                ->add('enabledFrom', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
                ->add('enabledTo', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
            ->end()
            ->with($this->trans('form_site.label_seo'), array('class' => 'col-md-6'))
                ->add('title', null, array('required' => false))
                ->add('metaDescription', 'textarea', array('required' => false))
                ->add('metaKeywords', 'textarea', array('required' => false))
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

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        $this->routePageGenerator->update($object);
    }
}
