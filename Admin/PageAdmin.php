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
use Knplabs\Bundle\MenuBundle\MenuItem;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\PageBundle\Model\PageInterface;

class PageAdmin extends Admin
{
    protected $cmsManager;

    protected $list = array(
        'hybrid' => array('type' => 'string', 'template' => 'SonataPageBundle:PageAdmin:field_hybrid.html.twig'),
        'name' => array('identifier' => true),
        'routeName',
        'decorate',
        'enabled',
    );

    protected $filter = array(
        'name',
    );

    protected $view = array(
        'routeName',
        'enabled',
        'decorate',
        'name',
        'slug',
        'customUrl'
    );

    protected $formGroups = array(
        'General' => array(
            'fields' => array('name', 'enabled')
        ),
        'Publication' => array(
            'fields' => array('parent', 'publicationDateStart', 'publicationDateEnd')
        ),
        'SEO' => array(
            'fields' => array('slug', 'customUrl', 'metaKeyword', 'metaDescription'),
            'collapsed' => true
        ),
        'Advanced' => array(
            'fields' => array('loginRequired', 'template', 'decorate', 'routeName', 'javascript', 'stylesheet'),
            'collapsed' => true
        )
    );

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('enabled', array('required' => false))
            ->add('decorate', array('required' => false))
            ->add('name')
            ->add('metaKeyword',  array('required' => false), array('type' => 'text'))
            ->add('metaDescription', array('required' => false), array('type' => 'text'))
            ->add('template', array('required' => false))
            ->add('publicationDateStart', array('required' => false))
            ->add('publicationDateEnd', array('required' => false))
            ->add('javascript', array('required' => false))
            ->add('stylesheet', array('required' => false))
        ;

        if (!$this->getSubject()->isHybrid()) {
            $formMapper
                ->add('slug', array('required' => false), array('type' => 'string'))
                ->add('customUrl', array('required' => false), array('type' => 'string'))
                ->add('parent', array(
                'query' => $this->modelManager
                    ->createQuery($this->getClass(), 'o')
                    ->where('o.routeName = :route_name')
                    ->setParameter('route_name', PageInterface::PAGE_ROUTE_CMS_NAME)
            ));
        }

        $formMapper->setHelps(array(
            'name' => $this->trans('help_page_name')
        ));
    }

    public function configureDatagridFilters(DatagridMapper $datagrid)
    {
        $datagrid->add('hybrid', array(
            'template' => 'SonataAdminBundle:CRUD:filter_callback.html.twig',
            'type' => 'callback',
            'filter_options' => array(
                'filter' => array($this, 'handleHybridFilter'),
                'type'   => 'choice'
            ),
            'filter_field_options' => array(
                'required' => false,
                'choices'  => array(
                    'hybrid'  => $this->trans('hybrid'),
                    'cms'     => $this->trans('cms'),
                )
            )
        ));
    }

    public function handleHybridFilter($queryBuilder, $alias, $field, $value)
    {
        if (!$value) {
            return;
        }

        $queryBuilder->andWhere(sprintf('%s.routeName %s :routeName', $alias, $value == 'cms' ? '=' : '!='));
        $queryBuilder->setParameter('routeName', PageInterface::PAGE_ROUTE_CMS_NAME);
    }

    public function configureSideMenu(MenuItem $menu, $action, Admin $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('edit_page'),
            $admin->generateUrl('edit', array('id' => $id))
        );

        $menu->addChild(
            $this->trans('view_page_blocks'),
            $admin->generateUrl('sonata.page.admin.block.list', array('id' => $id))
        );

        $menu->addChild(
            $this->trans('snapshot'),
            $admin->generateUrl('sonata.page.admin.snapshot.list', array('id' => $id))
        );

        if (!$this->getSubject()->isHybrid()) {
            $menu->addChild(
                $this->trans('view_page'),
                $this->getRouter()->getGenerator()->getContext()->getBaseUrl().$this->getSubject()->getSlug()
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

    public function setCmsManager(CmsPageManager $cmsManager)
    {
        $this->cmsManager= $cmsManager;
    }

    public function getNewInstance()
    {
        $instance = parent::getNewInstance();

        if ($this->hasRequest()) {
            $instance->setSlug($this->getRequest()->get('slug'));
            $instance->setName(str_replace(array('/', '-') , ' ', $instance->getSlug()));
            $instance->setRouteName(PageInterface::PAGE_ROUTE_CMS_NAME);
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