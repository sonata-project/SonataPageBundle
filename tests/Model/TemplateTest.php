<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\Template;

class TemplateTest extends TestCase
{
    public function testArea()
    {
        $template = new Template('page', 'template.twig');

        $template->addContainer('zone_A', []);
        $expected = [
            'zone_A' => [
                'name' => 'n/a',
                'type' => Template::TYPE_STATIC,
                'blocks' => [],
                'placement' => [],
                'shared' => false,
            ],
        ];
        $this->assertEquals($template->getContainers(), $expected);

        $template->addContainer('zone_B', [
            'shared' => true,
        ]);
        $expected['zone_B'] = [
            'name' => 'n/a',
            'type' => Template::TYPE_STATIC,
            'blocks' => [],
            'placement' => [],
            'shared' => true,
        ];
        $this->assertEquals($template->getContainers(), $expected);
    }

    public function testGetContainer()
    {
        $template = new Template('page', 'template.twig', ['header' => [
            'name' => 'Header',
            'block' => ['text.block'],
        ]]);

        $expected = [
            'name' => 'Header',
            'type' => Template::TYPE_STATIC,
            'blocks' => [],
            'placement' => [],
            'shared' => false,
        ];

        $this->assertEquals($expected, $template->getContainer('header'));
    }
}
