<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('sonata_page')->children();

        $node
            ->scalarNode('is_inline_edition_on')->defaultFalse()->end()
            ->scalarNode('use_streamed_response')->defaultFalse()->end()
            ->scalarNode('multisite')->isRequired()->end()

            ->arrayNode('ignore_route_patterns')
                ->defaultValue(array(
                    '/(.*)admin(.*)/',   # ignore admin route, ie route containing 'admin'
                    '/^_(.*)/',          # ignore symfony routes
                ))
                ->prototype('scalar')->end()
            ->end()

            ->arrayNode('ignore_routes')
                ->defaultValue(array(
                    'sonata_page_cache_esi',
                    'sonata_page_cache_ssi',
                    'sonata_page_js_sync_cache',
                    'sonata_page_js_async_cache',
                    'sonata_cache_esi',
                    'sonata_cache_js_async',
                    'sonata_cache_js_sync',
                    'sonata_cache_apc',
                ))
                ->prototype('scalar')->end()
            ->end()

            ->arrayNode('ignore_uri_patterns')
                ->defaultValue(array(
                    '/admin(.*)/',   # ignore admin route, ie route containing 'admin'
                ))
                ->prototype('scalar')->end()
            ->end()

            ->arrayNode('cache_invalidation')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('service')->defaultValue('sonata.cache.invalidation.simple')->end()
                    ->scalarNode('recorder')->defaultValue('sonata.cache.recorder')->end()
                    ->arrayNode('classes')
                        ->useAttributeAsKey('id')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()

            ->scalarNode('default_page_service')->defaultValue('sonata.page.service.default')->end()
            ->scalarNode('default_template')->isRequired()->end()

            ->arrayNode('templates')
                ->isRequired()
                ->useAttributeAsKey('id')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')->end()
                        ->scalarNode('path')->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('page_defaults')
                ->useAttributeAsKey('id')
                ->prototype('array')
                    ->children()
                        ->booleanNode('decorate')->defaultValue(true)->end()
                        ->booleanNode('enabled')->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('caches')
                ->children()
                    ->arrayNode('esi')
                        ->children()
                            ->scalarNode('token')->defaultValue(hash('sha256', uniqid(mt_rand(), true)))->end()
                            ->scalarNode('version')->defaultValue(2)->end()
                            ->arrayNode('servers')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('ssi')
                        ->children()
                            ->scalarNode('token')->defaultValue(hash('sha256', uniqid(mt_rand(), true)))->end()
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('catch_exceptions')
                ->useAttributeAsKey('id')
                ->prototype('variable')->isRequired()->end()
            ->end()

            ->scalarNode('entity_manager')->defaultValue('doctrine.orm.default_entity_manager')->end()

            ->arrayNode('class')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('page')->defaultValue('Application\\Sonata\\PageBundle\\Entity\\Page')->end()
                    ->scalarNode('snapshot')->defaultValue('Application\\Sonata\\PageBundle\\Entity\\Snapshot')->end()
                    ->scalarNode('block')->defaultValue('Application\\Sonata\\PageBundle\\Entity\\Block')->end()
                    ->scalarNode('site')->defaultValue('Application\\Sonata\\PageBundle\\Entity\\Site')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
