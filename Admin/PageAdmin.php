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

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;

use Knp\Bundle\MenuBundle\MenuItem;

class PageAdmin extends Admin
{
    protected $cmsManager;

    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
            ->add('routeName')
            ->add('enabled')
            ->add('decorate')
            ->add('name')
            ->add('slug')
            ->add('customUrl')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('hybrid', 'text', array('template' => 'SonataPageBundle:PageAdmin:field_hybrid.html.twig'))
            ->add('name', null, array('identifier' => true))
            ->add('routeName')
            ->add('decorate')
            ->add('enabled')
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('hybrid', 'doctrine_orm_callback', array(
                'callback' => function($queryBuilder, $alias, $field, $value) {
                    if ($value) {
                        $queryBuilder->andWhere(sprintf('%s.routeName %s :routeName', $alias, $value == 'cms' ? '=' : '!='));
                        $queryBuilder->setParameter('routeName', PageInterface::PAGE_ROUTE_CMS_NAME);
                    }
                },
                'field_options' => array(
                    'required' => false,
                    'choices'  => array(
                        'hybrid'  => $this->trans('hybrid'),
                        'cms'     => $this->trans('cms'),
                    )
                ),
                'field_type' => 'choice'
            ))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $templates = array();
        foreach ($this->cmsManager->getPageManager()->getTemplates() as $code => $template) {
            $templates[$code] = $template->getName();
        }

        $formMapper
            ->with($this->trans('form_page.group_main_label', array(), 'SonataAdminBundle'))
                ->add('name')
                ->add('enabled', null, array('required' => false))
                ->add('position')
                ->add('templateCode', 'choice', array('required' => true, 'choices' => $templates))
            ->end()
        ;

        if (!$this->getSubject() || !$this->getSubject()->isDynamic()) {
            $formMapper
                ->with($this->trans('form_page.group_main_label', array(), 'SonataAdminBundle'))
                    ->add('target', 'sonata_page_selector', array(
                        'page'          => $this->getSubject() ?: null,
                        'model_manager' => $this->getModelManager(),
                        'class'         => $this->getClass(),
                        'filter_choice' => array('request_method' => 'all'),
                        'required'      => false
                    ))
                ->end()
            ;
        }

        if (!$this->getSubject() || !$this->getSubject()->isHybrid()) {
            $formMapper
                ->with($this->trans('form_page.group_seo_label', array(), 'SonataAdminBundle'))
                    ->add('slug', 'text',  array('required' => false))
                    ->add('customUrl', 'text', array('required' => false))
                ->end()
            ;
        }

        $formMapper
            ->with($this->trans('form_page.group_seo_label', array(), 'SonataAdminBundle'), array('collapsed' => true))
                ->add('metaKeyword', 'textarea', array('required' => false))
                ->add('metaDescription', 'textarea', array('required' => false))
            ->end()
        ;

        if (!$this->getSubject() || !$this->getSubject()->isDynamic()) {
            $formMapper
                ->with($this->trans('form_page.group_main_label', array(), 'SonataAdminBundle'))
                    ->add('parent', 'sonata_page_selector', array(
                        'page'          => $this->getSubject() ?: null,
                        'model_manager' => $this->getModelManager(),
                        'class'         => $this->getClass(),
                        'filter_choice' => array('hierarchy' => 'root'),
                        'required'      => false
                    ))
                ->end()
            ;
        }

        $formMapper
            ->with($this->trans('form_page.group_advanced_label', array(), 'SonataAdminBundle'), array('collapsed' => true))
                ->add('decorate', null,  array('required' => false))
                ->add('javascript', null,  array('required' => false))
                ->add('stylesheet', null, array('required' => false))
                ->add('rawHeaders', null, array('required' => false))
            ->end()
        ;

        $formMapper->setHelps(array(
            'name' => $this->trans('help_page_name')
        ));
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('snapshots');
    }

    public function getListTemplate()
    {
        return 'SonataPageBundle:PageAdmin:list.html.twig';
    }

    protected function configureSideMenu(MenuItem $menu, $action, Admin $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('sidemenu.link_edit_page', array(), 'SonataAdminBundle'),
            $admin->generateUrl('edit', array('id' => $id))
        );

        $menu->addChild(
            $this->trans('sidemenu.link_list_blocks', array(), 'SonataAdminBundle'),
            $admin->generateUrl('sonata.page.admin.block.list', array('id' => $id))
        );

        $menu->addChild(
            $this->trans('sidemenu.link_list_snapshots', array(), 'SonataAdminBundle'),
            $admin->generateUrl('sonata.page.admin.snapshot.list', array('id' => $id))
        );

        if (!$this->getSubject()->isHybrid()) {
            $menu->addChild(
                $this->trans('view_page'),
                $this->getRouter()->getGenerator()->getContext()->getBaseUrl().$this->getSubject()->getUrl()
            );
        }
    }

    public function postUpdate($object)
    {
        $this->cmsManager->invalidate(new CacheElement(array(
           'page_id' => $object->getId()
        )));
    }

    public function update($object)
    {
        $this->preUpdate($object);
        $this->cmsManager->getPageManager()->save($object);
        $this->postUpdate($object);
    }

    public function create($object)
    {
        $this->prePersist($object);
        $this->cmsManager->getPageManager()->save($object);
        $this->postPersist($object);
    }

    public function setCmsManager(CmsManagerInterface $cmsManager)
    {
        $this->cmsManager= $cmsManager;
    }

    public function getNewInstance()
    {
        $instance = parent::getNewInstance();

        if ($this->hasRequest() && $this->getRequest()->get('url')) {
            $slugs  = explode('/', $this->getRequest()->get('url'));
            $slug   = array_pop($slugs);

            $parent = $this->cmsManager->getPageByUrl(implode('/', $slugs));
            if (!$parent) {
                $parent = $this->cmsManager->getPageByUrl('/');
            }

            $instance->setSlug(urldecode($slug));
            $instance->setParent($parent ?: null);
            $instance->setName(urldecode($slug));
        }

        return $instance;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        $actions['snapshot'] = $this->trans('create_snapshot');

        return $actions;
    }
}