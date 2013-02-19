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

/**
 * Admin definition for the Site class
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SiteAdmin extends Admin
{
    protected $cmsManager;

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
            ->with($this->trans('form_site.label_general'))
                ->add('name')
                ->add('isDefault', null, array('required' => false))
                ->add('enabled', null, array('required' => false))
                ->add('host')
                ->add('locale', null, array(
                    'required' => false
                ))
                ->add('relativePath', null, array('required' => false))
                ->add('enabledFrom')
                ->add('enabledTo')
            ->end()
            ->with($this->trans('form_site.label_seo'))
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
}
