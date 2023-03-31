<?php

declare(strict_types=1);

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

final class TemplateTest extends TestCase
{
    public function testArea(): void
    {
        $template = new Template('page', 'template.twig');

        $template->addContainer('zone_A', []);
        $expected = [
            'zone_A' => [
                'name' => 'n/a',
                'type' => Template::TYPE_STATIC,
                'blocks' => [],
                'placement' => null,
                'shared' => false,
            ],
        ];
        static::assertSame($template->getContainers(), $expected);

        $template->addContainer('zone_B', [
            'shared' => true,
        ]);
        $expected['zone_B'] = [
            'name' => 'n/a',
            'type' => Template::TYPE_STATIC,
            'blocks' => [],
            'placement' => null,
            'shared' => true,
        ];
        static::assertSame($template->getContainers(), $expected);
    }

    public function testGetContainer(): void
    {
        $template = new Template('page', 'template.twig', ['header' => [
            'name' => 'Header',
        ]]);

        $expected = [
            'name' => 'Header',
            'type' => Template::TYPE_STATIC,
            'blocks' => [],
            'placement' => null,
            'shared' => false,
        ];

        static::assertSame($expected, $template->getContainer('header'));
    }
}
