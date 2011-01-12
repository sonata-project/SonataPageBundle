<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\PageBundle\Admin;

use Bundle\Sonata\BaseApplicationBundle\Admin\Admin;

class TemplateAdmin extends Admin
{

    protected $class = 'Application\Sonata\PageBundle\Entity\Template';

    protected $list_fields = array(
        'name' => array('identifier' => true),
        'path',
        'enabled',
    );

    protected $base_route = 'sonata_page_template_admin';
}