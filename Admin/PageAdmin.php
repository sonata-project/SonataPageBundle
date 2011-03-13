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

class PageAdmin extends Admin
{

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


}