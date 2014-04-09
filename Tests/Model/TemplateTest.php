<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Entity;

use Sonata\PageBundle\Model\Template;

/**
 *
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testArea()
    {
        $template = new Template('page', 'template.twig');

        $template->addContainer('zone_A', array());
        $expected = array(
            'zone_A' => array(
                'name'      => 'n/a',
                'type'      => Template::TYPE_STATIC,
                'blocks'    => array(),
                'placement' => array(),
                'shared'    => false,
            ),
        );
        $this->assertEquals($template->getContainers(), $expected);

        $template->addContainer('zone_B', array(
            'shared' => true,
        ));
        $expected['zone_B'] = array(
            'name'      => 'n/a',
            'type'      => Template::TYPE_STATIC,
            'blocks'    => array(),
            'placement' => array(),
            'shared'    => true,
        );
        $this->assertEquals($template->getContainers(), $expected);
    }
}
