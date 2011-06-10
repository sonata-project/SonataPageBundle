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

class PageAdmin extends Admin
{

    protected $manager;

    protected $list = array(
        'name' => array('identifier' => true),
        'routeName',
        'decorate',
        'enabled',
    );

    protected $form = array(
        'routeName',
        'enabled',
        'decorate',
        'loginRequired',
        'name',
        'slug' => array('type' => 'string'),
        'customUrl' => array('type' => 'string'),
        'metaKeyword' => array('type' => 'text'),
        'metaDescription' => array('type' => 'text'),
        'template',
        'publicationDateStart',
        'publicationDateEnd',
        'javascript',
        'stylesheet',
    );

    protected $filter = array(
        'name',
        'loginRequired'
    );

    protected $formGroups = array(
        'General' => array(
            'fields' => array('name', 'enabled', 'publicationDateStart', 'publicationDateEnd')
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
    }

    public function postUpdate($object)
    {
        $this->manager->invalidate(new CacheElement(array(
           'page_id' => $object->getId()
        )));
    }

    public function setManager(CmsPageManager $manager)
    {
        $this->manager= $manager;
    }
}