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

        $template->addContainer('zone', array());

        $expected = array(
            'zone' => array(
                'name'      => 'n/a',
                'type'      => Template::TYPE_STATIC,
                'blocks'    => array(),
                'placement' => array(),
            ),
        );

        $this->assertEquals($template->getContainers(), $expected);
    }
}
