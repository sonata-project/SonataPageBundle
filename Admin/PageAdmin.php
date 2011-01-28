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

use Sonata\BaseApplicationBundle\Admin\EntityAdmin as Admin;

class PageAdmin extends Admin
{

    protected $class = 'Application\Sonata\PageBundle\Entity\Page';

    protected $listFields = array(
        'name' => array('identifier' => true),
        'route_name',
        'decorate',
        'enabled',
    );

    protected $formFields = array(
        'route_name',
        'enabled',
        'decorate',
        'login_required',
        'name',
        'slug' => array('type' => 'string'),
        'custom_url' => array('type' => 'string'),
        'meta_keyword' => array('type' => 'text'),
        'meta_description' => array('type' => 'text'),
        'template',
        'publication_date_start',
        'publication_date_end',
        'javascript',
        'stylesheet',
    );

    protected $formFroups = array(
        'General' => array(
            'fields' => array('name', 'enabled', 'publication_date_start', 'publication_date_end')
        ),
        'SEO' => array(
            'fields' => array('slug', 'custom_url', 'meta_keyword', 'meta_description'),
            'collapsed' => true
        ),
        'Advanced' => array(
            'fields' => array('login_required', 'template', 'decorate', 'route_name', 'javascript', 'stylesheet'),
            'collapsed' => true
        )
    );

    protected $baseControllerName = 'SonataPageBundle:PageAdmin';
}