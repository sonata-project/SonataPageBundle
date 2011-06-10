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

class SnapshotAdmin extends Admin
{
    protected $parentAssociationMapping = 'page';

    protected $list = array(
        'slug' => array('identifier' => true),
        'enabled',
        'publicationDateStart',
        'publicationDateEnd'
    );

    protected $form = array(
        'routeName',
        'enabled',
        'decorate',
        'loginRequired',
        'slug' => array('type' => 'string'),
        'customUrl' => array('type' => 'string'),
        'publicationDateStart',
        'publicationDateEnd',
    );

    protected $filter = array(
        'routeName',
    );
}