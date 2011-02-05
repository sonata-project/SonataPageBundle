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

class TemplateAdmin extends Admin
{

    protected $class = 'Application\Sonata\PageBundle\Entity\Template';
    protected $baseControllerName = 'SonataPageBundle:TemplateAdmin';

    protected $list = array(
        'name' => array('identifier' => true),
        'path',
        'enabled',
    );

    protected $form = array(
        'name',
        'path',
        'enabled',
        'content'
    );

    protected $filter = array(
        'name',
        'enabled'
    );

}