<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\DependencyInjection;

use Sonata\PageBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 *
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function testPageWithMatrix()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array(array(
            'multisite' => 'host_with_path',
            'default_template' => 'default',
            'templates' => array(
                'default' => array(
                    'path' => 'ApplicationSonataPageBundle::demo_layout.html.twig',
                    'name' => 'default',
                    'containers' => array('header' => array('name' => 'My Header')),
                    'matrix' => array('layout' => 'HHHH', 'mapping' => array('H' => 'header')),
                )
            )
        )));

        $expected = array(
            'multisite'             => 'host_with_path',
            'default_template'      => 'default',
            'templates'             => array(
                'default' => array(
                    'path'       => 'ApplicationSonataPageBundle::demo_layout.html.twig',
                    'name'       => 'default',
                    'containers' => array(
                        'header' => array(
                            'name'      => 'My Header',
                            'type'      => 1,
                            'shared'    => false,
                            'blocks'    => array(),
                            'placement' => array(
                                'x'      => 0,
                                'y'      => 0,
                                'width'  => 100,
                                'height' => 100,
                                'right'  => 0,
                                'bottom' => 0,
                            ),
                        ),
                    ),
                ),
            ),
            'assets' => array(
                'stylesheets' => array(
                    'bundles/sonataadmin/vendor/bootstrap/dist/css/bootstrap.min.css',
                    'bundles/sonatapage/sonata-page.front.css',
                ),
                'javascripts' => array(
                    'bundles/sonataadmin/vendor/jquery/dist/jquery.min.js',
                    'bundles/sonataadmin/vendor/bootstrap/dist/js/bootstrap.min.js',
                    'bundles/sonatapage/sonata-page.front.js',
                )
            ),
            'is_inline_edition_on'  => false,
            'use_streamed_response' => false,
            'ignore_route_patterns' => array(
                0 => '/(.*)admin(.*)/',
                1 => '/^_(.*)/',
            ),
            'ignore_routes'         => array(
                0 => 'sonata_page_cache_esi',
                1 => 'sonata_page_cache_ssi',
                2 => 'sonata_page_js_sync_cache',
                3 => 'sonata_page_js_async_cache',
                4 => 'sonata_cache_esi',
                5 => 'sonata_cache_js_async',
                6 => 'sonata_cache_js_sync',
                7 => 'sonata_cache_apc',
            ),
            'ignore_uri_patterns' => array(
                0 => '/admin(.*)/',
            ),
            'cache_invalidation' => array(
                'service'  => 'sonata.cache.invalidation.simple',
                'recorder' => 'sonata.cache.recorder',
                'classes'  => array(),
            ),
            'default_page_service'  => 'sonata.page.service.default',
            'page_defaults'         => array(),
            'catch_exceptions'      => array(),
            'class'                 => array(
                'page'     => 'Application\\Sonata\\PageBundle\\Entity\\Page',
                'snapshot' => 'Application\\Sonata\\PageBundle\\Entity\\Snapshot',
                'block'    => 'Application\\Sonata\\PageBundle\\Entity\\Block',
                'site'     => 'Application\\Sonata\\PageBundle\\Entity\\Site',
            ),

            'slugify_service' => 'sonata.core.slugify.native',
            'direct_publication' => false,
        );

        $this->assertEquals($expected, $config);
    }

    public function testPageWithoutMatrix()
    {
        $processor = new Processor();

        $config = $processor->processConfiguration(new Configuration(), array(array(
            'multisite' => 'host_with_path',
            'default_template' => 'default',
            'templates' => array(
                'default' => array(
                    'path' => 'ApplicationSonataPageBundle::demo_layout.html.twig',
                    'name' => 'default',
                )
            )
        )));

        $expected = array(
            'multisite'             => 'host_with_path',
            'default_template'      => 'default',
            'templates'             => array(
                'default' => array(
                    'path'       => 'ApplicationSonataPageBundle::demo_layout.html.twig',
                    'name'       => 'default',
                    'containers' => array(),
                ),
            ),
            'assets' => array(
                'stylesheets' => array(
                    'bundles/sonataadmin/vendor/bootstrap/dist/css/bootstrap.min.css',
                    'bundles/sonatapage/sonata-page.front.css',
                ),
                'javascripts' => array(
                    'bundles/sonataadmin/vendor/jquery/dist/jquery.min.js',
                    'bundles/sonataadmin/vendor/bootstrap/dist/js/bootstrap.min.js',
                    'bundles/sonatapage/sonata-page.front.js',
                )
            ),
            'is_inline_edition_on'  => false,
            'use_streamed_response' => false,
            'ignore_route_patterns' => array(
                0 => '/(.*)admin(.*)/',
                1 => '/^_(.*)/',
            ),
            'ignore_routes'         => array(
                0 => 'sonata_page_cache_esi',
                1 => 'sonata_page_cache_ssi',
                2 => 'sonata_page_js_sync_cache',
                3 => 'sonata_page_js_async_cache',
                4 => 'sonata_cache_esi',
                5 => 'sonata_cache_js_async',
                6 => 'sonata_cache_js_sync',
                7 => 'sonata_cache_apc',
            ),
            'ignore_uri_patterns' => array(
                0 => '/admin(.*)/',
            ),
            'cache_invalidation' => array(
                'service'  => 'sonata.cache.invalidation.simple',
                'recorder' => 'sonata.cache.recorder',
                'classes'  => array(),
            ),
            'default_page_service'  => 'sonata.page.service.default',
            'page_defaults'         => array(),
            'catch_exceptions'      => array(),
            'class'                 => array(
                'page'     => 'Application\\Sonata\\PageBundle\\Entity\\Page',
                'snapshot' => 'Application\\Sonata\\PageBundle\\Entity\\Snapshot',
                'block'    => 'Application\\Sonata\\PageBundle\\Entity\\Block',
                'site'     => 'Application\\Sonata\\PageBundle\\Entity\\Site',
            ),

            'slugify_service' => 'sonata.core.slugify.native',
            'direct_publication' => false,
        );

        $this->assertEquals($expected, $config);
    }
}
