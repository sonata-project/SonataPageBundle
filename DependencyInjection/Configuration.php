<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\DependencyInjection;

use Sonata\PageBundle\Model\Template;
use Sonata\PageBundle\Template\Matrix\Parser;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('sonata_page')->children();

        $ignoreRoutePatternsInfo = <<<'EOF'
(.*)admin(.*)       ignore admin route, i.e. route containing 'admin'
^_(.*)              ignore Symfony routes
EOF;

        $ignoreUriPatternsInfo = <<<'EOF'
admin(.*)           ignore admin route, i.e. route containing 'admin'
EOF;

        $pageDefaultsInfo = <<<'EOF'
Example:
homepage: { decorate: false }       disable decoration for 'homepage', key is a page route
EOF;

        $catchExceptionsInfo = <<<'EOF'
Manage the HTTP errors

Examples:
not_found: [404]    render 404 page with "not_found" key (name generated: _page_internal_error_not_found)
fatal:     [500]    render 500 page with "fatal" key (name generated: _page_internal_error_fatal)
EOF;

        $directPublicationInfo = <<<'EOF'
Generates a snapshot when a page is saved from the admin.

You can use %kernel.debug%, if you want to publish in dev mode, but not in prod.
EOF;

        $node
            ->scalarNode('is_inline_edition_on')
                ->defaultFalse()
            ->end()
            ->scalarNode('hide_disabled_blocks')
                ->defaultFalse()
            ->end()
            ->scalarNode('use_streamed_response')
                ->info('Set the value to false in debug mode or if the reverse proxy does not handle streamed response')
                ->defaultFalse()
            ->end()
            ->scalarNode('multisite')
                ->info('For more information, see https://sonata-project.org/bundles/page/master/doc/reference/multisite.html')
                ->isRequired()
                ->validate()
                    ->ifNotInArray(['host', 'host_by_locale', 'host_with_path', 'host_with_path_by_locale'])
                    ->thenInvalid('Invalid multisite configuration %s. For more information, see https://sonata-project.org/bundles/page/master/doc/reference/multisite.html')
                ->end()
            ->end()
            ->arrayNode('ignore_route_patterns')
                ->info($ignoreRoutePatternsInfo)
                ->defaultValue([
                    '(.*)admin(.*)',
                    '^_(.*)',
                ])
                ->prototype('scalar')->end()
            ->end()
            ->scalarNode('slugify_service')
                // NEXT_MAJOR: reword this info message
                ->info('You should use: sonata.core.slugify.cocur, but for BC we keep \'sonata.core.slugify.native\' as default')

                // NEXT_MAJOR: use "sonata.core.slugify.cocur" instead of "sonata.core.slugify.native" as default value
                ->defaultValue('sonata.core.slugify.native')
            ->end()
            ->arrayNode('ignore_routes')
                ->defaultValue([
                    'sonata_page_cache_esi',
                    'sonata_page_cache_ssi',
                    'sonata_page_js_sync_cache',
                    'sonata_page_js_async_cache',
                    'sonata_cache_esi',
                    'sonata_cache_js_async',
                    'sonata_cache_js_sync',
                    'sonata_cache_apc',
                ])
                ->prototype('scalar')->end()
            ->end()

            ->arrayNode('ignore_uri_patterns')
                ->info($ignoreUriPatternsInfo)
                ->defaultValue([
                    'admin(.*)',
                ])
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

            ->scalarNode('default_page_service')
                ->defaultValue('sonata.page.service.default')
            ->end()
            ->scalarNode('default_template')
                ->info('Template key from templates section, used as default for pages')
                ->isRequired()
            ->end()

            ->arrayNode('assets')
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode('stylesheets')
                        ->defaultValue([
                                'bundles/sonatacore/vendor/bootstrap/dist/css/bootstrap.min.css',
                                'bundles/sonatapage/sonata-page.front.css',
                            ])
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('javascripts')
                        ->defaultValue([
                                'bundles/sonatacore/vendor/jquery/dist/jquery.min.js',
                                'bundles/sonatacore/vendor/bootstrap/dist/js/bootstrap.min.js',
                                'bundles/sonatapage/sonata-page.front.js',
                            ])
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('templates')
                ->isRequired()
                ->useAttributeAsKey('id')
                ->prototype('array')
                    ->children()
                        ->scalarNode('name')->end()
                        ->scalarNode('path')->end()
                        ->scalarNode('inherits_containers')->end()
                        ->arrayNode('containers')
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->booleanNode('shared')
                                        ->defaultValue(false)
                                    ->end()
                                    ->scalarNode('type')
                                        ->defaultValue(Template::TYPE_STATIC)
                                    ->end()
                                    ->arrayNode('blocks')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('matrix')
                            ->children()
                                ->scalarNode('layout')
                                    ->isRequired()
                                ->end()
                                ->arrayNode('mapping')
                                    ->isRequired()
                                    ->requiresAtLeastOneElement()
                                    ->prototype('scalar')->isRequired()->end()
                                ->end()
                            ->end()
                            ->validate()
                            ->always()
                                ->then(function ($matrix) {
                                    return Parser::parse($matrix['layout'], $matrix['mapping']);
                                })
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->validate()
                ->always()
                    ->then(function ($templates) {
                        foreach ($templates as $id => &$template) {
                            if (0 == count($template['containers'])) {
                                continue;
                            }

                            if (isset($template['inherits_containers'])) {
                                $inherits = $template['inherits_containers'];
                                if (!isset($templates[$inherits])) {
                                    throw new InvalidConfigurationException(
                                        sprintf('Template "%s" cannot inherit containers from undefined template "%s"', $id, $inherits)
                                    );
                                }
                                $template['containers'] = array_merge($templates[$inherits]['containers'], $template['containers']);
                            }

                            foreach ($template['containers'] as $containerKey => $container) {
                                if (!isset($template['matrix'][$containerKey])) {
                                    throw new InvalidConfigurationException(
                                        sprintf('No area defined in matrix for template container "%s"', $containerKey)
                                    );
                                }
                            }

                            foreach ($template['matrix'] as $containerKey => $config) {
                                if (!isset($template['containers'][$containerKey])) {
                                    throw new InvalidConfigurationException(
                                        sprintf('No container defined for matrix area "%s"', $containerKey)
                                    );
                                }
                                $template['containers'][$containerKey]['placement'] = $config;
                            }
                            unset($template['inherits_containers'], $template['matrix']);
                        }

                        return $templates;
                    })
                ->end()
            ->end()

            ->arrayNode('templates_admin')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('list')
                        ->defaultValue('SonataPageBundle:PageAdmin:list.html.twig')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('tree')
                        ->defaultValue('SonataPageBundle:PageAdmin:tree.html.twig')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('compose')
                        ->defaultValue('SonataPageBundle:PageAdmin:compose.html.twig')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('compose_container_show')
                        ->defaultValue('SonataPageBundle:PageAdmin:compose_container_show.html.twig')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('select_site')
                        ->defaultValue('SonataPageBundle:PageAdmin:select_site.html.twig')
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('page_defaults')
                ->info($pageDefaultsInfo)
                ->useAttributeAsKey('id')
                ->prototype('array')
                    ->children()
                        ->booleanNode('decorate')
                            ->defaultValue(true)
                    ->end()
                        ->booleanNode('enabled')
                            ->defaultValue(true)
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('caches')
                ->children()
                    ->arrayNode('esi')
                        ->children()
                            ->scalarNode('token')
                                ->info('The default value is auto generated every time')
                                ->defaultValue(hash('sha256', uniqid(mt_rand(), true)))
                            ->end()
                            ->scalarNode('version')
                                ->defaultValue(2)
                            ->end()
                            ->arrayNode('servers')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('ssi')
                        ->children()
                            ->scalarNode('token')
                                ->info('The default value is auto generated every time')
                                ->defaultValue(hash('sha256', uniqid(mt_rand(), true)))
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('catch_exceptions')
                ->info($catchExceptionsInfo)
                ->useAttributeAsKey('id')
                ->prototype('variable')->isRequired()->end()
            ->end()

            ->arrayNode('class')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('page')
                        ->defaultValue('Application\\Sonata\\PageBundle\\Entity\\Page')
                    ->end()
                    ->scalarNode('snapshot')
                        ->defaultValue('Application\\Sonata\\PageBundle\\Entity\\Snapshot')
                    ->end()
                    ->scalarNode('block')
                        ->defaultValue('Application\\Sonata\\PageBundle\\Entity\\Block')
                    ->end()
                    ->scalarNode('site')
                        ->defaultValue('Application\\Sonata\\PageBundle\\Entity\\Site')
                    ->end()
                ->end()
            ->end()
            ->booleanNode('direct_publication')
                ->info($directPublicationInfo)
                ->defaultValue(false)
            ->end()
        ;

        return $treeBuilder;
    }
}
