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

namespace Sonata\PageBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    public function testPageWithMatrix(): void
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), [[
            'multisite' => 'host_with_path',
            'default_template' => 'default',
            'templates' => [
                'default' => [
                    'path' => '@ApplicationSonataPage/demo_layout.html.twig',
                    'name' => 'default',
                    'containers' => ['header' => ['name' => 'My Header']],
                    'matrix' => ['layout' => 'HHHH', 'mapping' => ['H' => 'header']],
                ],
                '2columns' => [
                    'path' => '@ApplicationSonataPage/demo_2columns_layout.html.twig',
                    'name' => '2 columns layout',
                    'inherits_containers' => 'default',
                    'containers' => [
                        'left_col' => ['name' => 'Left column'],
                    ],
                    'matrix' => ['layout' => 'HHHHLLLL', 'mapping' => ['H' => 'header', 'L' => 'left_col']],
                ],
            ],
        ]]);

        $expected = [
            'multisite' => 'host_with_path',
            'default_template' => 'default',
            'templates' => [
                'default' => [
                    'path' => '@ApplicationSonataPage/demo_layout.html.twig',
                    'name' => 'default',
                    'containers' => [
                        'header' => [
                            'name' => 'My Header',
                            'shared' => false,
                            'type' => 1,
                            'blocks' => [],
                            'placement' => [
                                'x' => 0,
                                'y' => 0,
                                'width' => 100,
                                'height' => 100,
                                'right' => 0,
                                'bottom' => 0,
                            ],
                        ],
                    ],
                ],
                '2columns' => [
                    'path' => '@ApplicationSonataPage/demo_2columns_layout.html.twig',
                    'name' => '2 columns layout',
                    'containers' => [
                        'header' => [
                            'name' => 'My Header',
                            'shared' => false,
                            'type' => 1,
                            'blocks' => [],
                            'placement' => [
                                'x' => 0,
                                'y' => 0,
                                'width' => 50.0,
                                'height' => 100,
                                'right' => 50.0,
                                'bottom' => 0,
                            ],
                        ],
                        'left_col' => [
                            'name' => 'Left column',
                            'shared' => false,
                            'type' => 1,
                            'blocks' => [],
                            'placement' => [
                                'x' => 50.0,
                                'y' => 0,
                                'width' => 50.0,
                                'height' => 100,
                                'right' => 0.0,
                                'bottom' => 0,
                            ],
                        ],
                    ],
                ],
            ],
            'skip_redirection' => false,
            'hide_disabled_blocks' => false,
            'use_streamed_response' => false,
            'router_auto_register' => [
                'enabled' => false,
                'priority' => 150,
            ],
            'ignore_route_patterns' => [
                0 => '(.*)admin(.*)',
                1 => '^_(.*)',
            ],
            'ignore_routes' => [
            ],
            'ignore_uri_patterns' => [
                0 => 'admin(.*)',
            ],
            'default_page_service' => 'sonata.page.service.default',
            'assets' => [
                'stylesheets' => [
                    'bundles/sonatapage/sonata-page.front.css',
                ],
                'javascripts' => [],
            ],
            'templates_admin' => [
                'list' => '@SonataPage/PageAdmin/list.html.twig',
                'tree' => '@SonataPage/PageAdmin/tree.html.twig',
                'compose' => '@SonataPage/PageAdmin/compose.html.twig',
                'compose_container_show' => '@SonataPage/PageAdmin/compose_container_show.html.twig',
                'select_site' => '@SonataPage/PageAdmin/select_site.html.twig',
            ],
            'page_defaults' => [],
            'catch_exceptions' => [],
            'class' => [
                    'page' => 'Application\\Sonata\\PageBundle\\Entity\\Page',
                    'snapshot' => 'Application\\Sonata\\PageBundle\\Entity\\Snapshot',
                    'block' => 'Application\\Sonata\\PageBundle\\Entity\\Block',
                    'site' => 'Application\\Sonata\\PageBundle\\Entity\\Site',
            ],
            'direct_publication' => false,
        ];

        static::assertSame($expected, $config);
    }

    public function testPageWithoutMatrix(): void
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), [[
            'multisite' => 'host_with_path',
            'default_template' => 'default',
            'templates' => [
                'default' => [
                    'path' => '@ApplicationSonataPage/demo_layout.html.twig',
                    'name' => 'default',
                ],
            ],
        ]]);

        $expected = [
            'multisite' => 'host_with_path',
            'default_template' => 'default',
            'templates' => [
                'default' => [
                    'path' => '@ApplicationSonataPage/demo_layout.html.twig',
                    'name' => 'default',
                    'containers' => [],
                ],
            ],
            'skip_redirection' => false,
            'hide_disabled_blocks' => false,
            'use_streamed_response' => false,
            'router_auto_register' => [
                'enabled' => false,
                'priority' => 150,
            ],
            'ignore_route_patterns' => [
                0 => '(.*)admin(.*)',
                1 => '^_(.*)',
            ],
            'ignore_routes' => [
            ],
            'ignore_uri_patterns' => [
                0 => 'admin(.*)',
            ],
            'default_page_service' => 'sonata.page.service.default',
            'assets' => [
                'stylesheets' => [
                    'bundles/sonatapage/sonata-page.front.css',
                ],
                'javascripts' => [],
            ],
            'templates_admin' => [
                'list' => '@SonataPage/PageAdmin/list.html.twig',
                'tree' => '@SonataPage/PageAdmin/tree.html.twig',
                'compose' => '@SonataPage/PageAdmin/compose.html.twig',
                'compose_container_show' => '@SonataPage/PageAdmin/compose_container_show.html.twig',
                'select_site' => '@SonataPage/PageAdmin/select_site.html.twig',
            ],
            'page_defaults' => [],
            'catch_exceptions' => [],
            'class' => [
                'page' => 'Application\\Sonata\\PageBundle\\Entity\\Page',
                'snapshot' => 'Application\\Sonata\\PageBundle\\Entity\\Snapshot',
                'block' => 'Application\\Sonata\\PageBundle\\Entity\\Block',
                'site' => 'Application\\Sonata\\PageBundle\\Entity\\Site',
            ],
            'direct_publication' => false,
        ];

        static::assertSame($expected, $config);
    }
}
