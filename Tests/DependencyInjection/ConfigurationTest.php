<?php

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

class ConfigurationTest extends TestCase
{
    public function testPageWithMatrix()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), [[
            'multisite' => 'host_with_path',
            'default_template' => 'default',
            'templates' => [
                'default' => [
                    'path' => 'ApplicationSonataPageBundle::demo_layout.html.twig',
                    'name' => 'default',
                    'containers' => ['header' => ['name' => 'My Header']],
                    'matrix' => ['layout' => 'HHHH', 'mapping' => ['H' => 'header']],
                ],
                '2columns' => [
                    'path' => 'ApplicationSonataPageBundle::demo_2columns_layout.html.twig',
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
                    'path' => 'ApplicationSonataPageBundle::demo_layout.html.twig',
                    'name' => 'default',
                    'containers' => [
                        'header' => [
                            'name' => 'My Header',
                            'type' => 1,
                            'shared' => false,
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
                    'path' => 'ApplicationSonataPageBundle::demo_2columns_layout.html.twig',
                    'name' => '2 columns layout',
                    'containers' => [
                        'left_col' => [
                            'name' => 'Left column',
                            'blocks' => [],
                            'shared' => false,
                            'type' => 1,
                            'placement' => [
                                'x' => 50.0,
                                'y' => 0,
                                'width' => 50.0,
                                'height' => 100,
                                'right' => 0,
                                'bottom' => 0,
                            ],
                        ],
                        'header' => [
                            'name' => 'My Header',
                            'type' => 1,
                            'shared' => false,
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
                    ],
                ],
            ],
            'templates_admin' => [
                'tree' => 'SonataPageBundle:PageAdmin:tree.html.twig',
                'list' => 'SonataPageBundle:PageAdmin:list.html.twig',
                'compose' => 'SonataPageBundle:PageAdmin:compose.html.twig',
                'select_site' => 'SonataPageBundle:PageAdmin:select_site.html.twig',
                'compose_container_show' => 'SonataPageBundle:PageAdmin:compose_container_show.html.twig',
            ],
            'assets' => [
                'stylesheets' => [
                    'bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css',
                    'bundles/sonatapage/sonata-page.front.css',
                ],
                'javascripts' => [
                    'bundles/sonatacore/vendor/jquery/dist/jquery.min.js',
                    'bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js',
                    'bundles/sonatapage/sonata-page.front.js',
                ],
            ],
            'is_inline_edition_on' => false,
            'hide_disabled_blocks' => false,
            'use_streamed_response' => false,
            'ignore_route_patterns' => [
                0 => '(.*)admin(.*)',
                1 => '^_(.*)',
            ],
            'ignore_routes' => [
                0 => 'sonata_page_cache_esi',
                1 => 'sonata_page_cache_ssi',
                2 => 'sonata_page_js_sync_cache',
                3 => 'sonata_page_js_async_cache',
                4 => 'sonata_cache_esi',
                5 => 'sonata_cache_js_async',
                6 => 'sonata_cache_js_sync',
                7 => 'sonata_cache_apc',
            ],
            'ignore_uri_patterns' => [
                0 => 'admin(.*)',
            ],
            'cache_invalidation' => [
                'service' => 'sonata.cache.invalidation.simple',
                'recorder' => 'sonata.cache.recorder',
                'classes' => [],
            ],
            'default_page_service' => 'sonata.page.service.default',
            'page_defaults' => [],
            'catch_exceptions' => [],
            'class' => [
                'page' => 'Application\\Sonata\\PageBundle\\Entity\\Page',
                'snapshot' => 'Application\\Sonata\\PageBundle\\Entity\\Snapshot',
                'block' => 'Application\\Sonata\\PageBundle\\Entity\\Block',
                'site' => 'Application\\Sonata\\PageBundle\\Entity\\Site',
            ],

            'slugify_service' => 'sonata.core.slugify.native',
            'direct_publication' => false,
        ];

        $this->assertEquals($expected, $config);
    }

    public function testPageWithoutMatrix()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), [[
            'multisite' => 'host_with_path',
            'default_template' => 'default',
            'templates' => [
                'default' => [
                    'path' => 'ApplicationSonataPageBundle::demo_layout.html.twig',
                    'name' => 'default',
                ],
            ],
        ]]);

        $expected = [
            'multisite' => 'host_with_path',
            'default_template' => 'default',
            'templates' => [
                'default' => [
                    'path' => 'ApplicationSonataPageBundle::demo_layout.html.twig',
                    'name' => 'default',
                    'containers' => [],
                ],
            ],
            'templates_admin' => [
                'tree' => 'SonataPageBundle:PageAdmin:tree.html.twig',
                'list' => 'SonataPageBundle:PageAdmin:list.html.twig',
                'compose' => 'SonataPageBundle:PageAdmin:compose.html.twig',
                'select_site' => 'SonataPageBundle:PageAdmin:select_site.html.twig',
                'compose_container_show' => 'SonataPageBundle:PageAdmin:compose_container_show.html.twig',
            ],
            'assets' => [
                'stylesheets' => [
                    'bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css',
                    'bundles/sonatapage/sonata-page.front.css',
                ],
                'javascripts' => [
                    'bundles/sonatacore/vendor/jquery/dist/jquery.min.js',
                    'bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js',
                    'bundles/sonatapage/sonata-page.front.js',
                ],
            ],
            'is_inline_edition_on' => false,
            'hide_disabled_blocks' => false,
            'use_streamed_response' => false,
            'ignore_route_patterns' => [
                0 => '(.*)admin(.*)',
                1 => '^_(.*)',
            ],
            'ignore_routes' => [
                0 => 'sonata_page_cache_esi',
                1 => 'sonata_page_cache_ssi',
                2 => 'sonata_page_js_sync_cache',
                3 => 'sonata_page_js_async_cache',
                4 => 'sonata_cache_esi',
                5 => 'sonata_cache_js_async',
                6 => 'sonata_cache_js_sync',
                7 => 'sonata_cache_apc',
            ],
            'ignore_uri_patterns' => [
                0 => 'admin(.*)',
            ],
            'cache_invalidation' => [
                'service' => 'sonata.cache.invalidation.simple',
                'recorder' => 'sonata.cache.recorder',
                'classes' => [],
            ],
            'default_page_service' => 'sonata.page.service.default',
            'page_defaults' => [],
            'catch_exceptions' => [],
            'class' => [
                'page' => 'Application\\Sonata\\PageBundle\\Entity\\Page',
                'snapshot' => 'Application\\Sonata\\PageBundle\\Entity\\Snapshot',
                'block' => 'Application\\Sonata\\PageBundle\\Entity\\Block',
                'site' => 'Application\\Sonata\\PageBundle\\Entity\\Site',
            ],

            'slugify_service' => 'sonata.core.slugify.native',
            'direct_publication' => false,
        ];

        $this->assertEquals($expected, $config);
    }
}
