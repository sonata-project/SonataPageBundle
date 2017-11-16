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

use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataPageExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $bundles = $container->getParameter('kernel.bundles');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('page.xml');

        // NEXT_MAJOR: remove this code block
        $definition = $container->getDefinition('sonata.page.router.request_context');
        $definition->setFactory([
            new Reference('sonata.page.site.selector'),
            'getRequestContext',
        ]);

        if (isset($bundles['FOSRestBundle']) && isset($bundles['NelmioApiDocBundle'])) {
            $loader->load('serializer.xml');

            $loader->load('api_controllers.xml');
            $loader->load('api_form.xml');
        }

        if (isset($bundles['SonataAdminBundle'])) {
            $loader->load('admin.xml');

            if (!$config['direct_publication']) {
                $container->removeDefinition('sonata.page.admin.extension.snapshot');
            }

            $this->configureTemplatesAdmin($container, $config);
        }

        $loader->load('block.xml');
        $loader->load('orm.xml');
        $loader->load('form.xml');
        $loader->load('cache.xml');
        $loader->load('twig.xml');
        $loader->load('http_kernel.xml');
        $loader->load('consumer.xml');
        $loader->load('validators.xml');

        $this->configureMultisite($container, $config);
        $this->configureCache($container, $config);
        $this->configureTemplates($container, $config);
        $this->configureExceptions($container, $config);
        $this->configurePageDefaults($container, $config);
        $this->configurePageServices($container, $config);

        $container->setParameter('sonata.page.assets', $config['assets']);
        $container->setParameter('sonata.page.slugify_service', $config['slugify_service']);

        $container->setParameter('sonata.page.is_inline_edition_on', $config['is_inline_edition_on']);
        $container->setParameter('sonata.page.hide_disabled_blocks', $config['hide_disabled_blocks']);
        $container->getDefinition('sonata.page.decorator_strategy')
            ->replaceArgument(0, $config['ignore_routes'])
            ->replaceArgument(1, $config['ignore_route_patterns'])
            ->replaceArgument(2, $config['ignore_uri_patterns']);

        $this->registerDoctrineMapping($config);
        $this->registerParameters($container, $config);
    }

    /**
     * Configure the page default settings.
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Array of configuration
     */
    public function configurePageDefaults(ContainerBuilder $container, array $config)
    {
        $defaults = [
            'templateCode' => $config['default_template'],
            'enabled' => true,
            'routeName' => null,
            'name' => null,
            'slug' => null,
            'url' => null,
            'requestMethod' => null,
            'decorate' => true,
        ];

        $container->getDefinition('sonata.page.manager.page')
            ->replaceArgument(2, $defaults);

        foreach ($config['page_defaults'] as $name => $pageDefaults) {
            $config['page_defaults'][$name] = array_merge($defaults, $pageDefaults);
        }

        $container->getDefinition('sonata.page.manager.page')
            ->replaceArgument(3, $config['page_defaults']);
    }

    /**
     * Registers service parameters from bundle configuration.
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Array of configuration
     */
    public function registerParameters(ContainerBuilder $container, array $config)
    {
        $container->setParameter('sonata.page.site.class', $config['class']['site']);
        $container->setParameter('sonata.page.block.class', $config['class']['block']);
        $container->setParameter('sonata.page.snapshot.class', $config['class']['snapshot']);
        $container->setParameter('sonata.page.page.class', $config['class']['page']);

        $container->setParameter('sonata.page.admin.site.entity', $config['class']['site']);
        $container->setParameter('sonata.page.admin.block.entity', $config['class']['block']);
        $container->setParameter('sonata.page.admin.snapshot.entity', $config['class']['snapshot']);
        $container->setParameter('sonata.page.admin.page.entity', $config['class']['page']);
    }

    /**
     * Registers doctrine mapping on concrete page entities.
     *
     * @param array $config
     */
    public function registerDoctrineMapping(array $config)
    {
        if (!class_exists($config['class']['page'])) {
            return;
        }

        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['class']['page'], 'mapOneToMany', [
            'fieldName' => 'children',
            'targetEntity' => $config['class']['page'],
            'cascade' => [
                'persist',
             ],
            'mappedBy' => 'parent',
            'orphanRemoval' => false,
            'orderBy' => [
                'position' => 'ASC',
            ],
        ]);

        $collector->addAssociation($config['class']['page'], 'mapOneToMany', [
            'fieldName' => 'blocks',
            'targetEntity' => $config['class']['block'],
            'cascade' => [
                'remove',
                'persist',
                'refresh',
                'merge',
                'detach',
            ],
            'mappedBy' => 'page',
            'orphanRemoval' => false,
            'orderBy' => [
                'position' => 'ASC',
            ],
        ]);

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', [
            'fieldName' => 'site',
            'targetEntity' => $config['class']['site'],
            'cascade' => [
                'persist',
            ],
            'mappedBy' => null,
            'inversedBy' => null,
            'joinColumns' => [
                [
                    'name' => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', [
            'fieldName' => 'parent',
            'targetEntity' => $config['class']['page'],
            'cascade' => [
                 'persist',
            ],
            'mappedBy' => null,
            'inversedBy' => 'children',
            'joinColumns' => [
                [
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['page'], 'mapOneToMany', [
             'fieldName' => 'sources',
             'targetEntity' => $config['class']['page'],
             'cascade' => [],
             'mappedBy' => 'target',
             'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', [
            'fieldName' => 'target',
            'targetEntity' => $config['class']['page'],
            'cascade' => [
                'persist',
            ],
            'mappedBy' => null,
            'inversedBy' => 'sources',
            'joinColumns' => [
                [
                    'name' => 'target_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['block'], 'mapOneToMany', [
            'fieldName' => 'children',
            'targetEntity' => $config['class']['block'],
            'cascade' => [
                'remove',
                'persist',
            ],
            'mappedBy' => 'parent',
            'orphanRemoval' => true,
            'orderBy' => [
                'position' => 'ASC',
            ],
        ]);

        $collector->addAssociation($config['class']['block'], 'mapManyToOne', [
            'fieldName' => 'parent',
            'targetEntity' => $config['class']['block'],
            'cascade' => [
            ],
            'mappedBy' => null,
            'inversedBy' => 'children',
            'joinColumns' => [
                [
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['block'], 'mapManyToOne', [
            'fieldName' => 'page',
            'targetEntity' => $config['class']['page'],
            'cascade' => [
                'persist',
            ],
            'mappedBy' => null,
            'inversedBy' => 'blocks',
            'joinColumns' => [
                [
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['snapshot'], 'mapManyToOne', [
            'fieldName' => 'site',
            'targetEntity' => $config['class']['site'],
            'cascade' => [
                'persist',
            ],
            'mappedBy' => null,
            'inversedBy' => null,
            'joinColumns' => [
                [
                    'name' => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['snapshot'], 'mapManyToOne', [
            'fieldName' => 'page',
            'targetEntity' => $config['class']['page'],
            'cascade' => [
                'persist',
            ],
            'mappedBy' => null,
            'inversedBy' => null,
            'joinColumns' => [
                [
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addIndex($config['class']['snapshot'], 'idx_snapshot_dates_enabled', [
            'publication_date_start',
            'publication_date_end',
            'enabled',
        ]);
    }

    /**
     * Configure the multi-site feature.
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Array of configuration
     */
    public function configureMultisite(ContainerBuilder $container, array $config)
    {
        $multisite = $config['multisite'];

        if ('host' === $multisite) {
            $container->setAlias('sonata.page.site.selector', 'sonata.page.site.selector.host');

            $container->removeDefinition('sonata.page.site.selector.host_by_locale');
            $container->removeDefinition('sonata.page.site.selector.host_with_path');
            $container->removeDefinition('sonata.page.site.selector.host_with_path_by_locale');
        } elseif ('host_by_locale' === $multisite) {
            $container->setAlias('sonata.page.site.selector', 'sonata.page.site.selector.host_by_locale');

            $container->removeDefinition('sonata.page.site.selector.host');
            $container->removeDefinition('sonata.page.site.selector.host_with_path');
            $container->removeDefinition('sonata.page.site.selector.host_with_path_by_locale');
        } else {
            /*
             * The multipath option required a specific router and RequestContext
             */
            $container->setAlias('router.request_context', 'sonata.page.router.request_context');

            if ('host_with_path' === $multisite) {
                $container->setAlias('sonata.page.site.selector', 'sonata.page.site.selector.host_with_path');

                $container->removeDefinition('sonata.page.site.selector.host_with_path_by_locale');
                $container->removeDefinition('sonata.page.site.selector.host');
                $container->removeDefinition('sonata.page.site.selector.host_by_locale');
            } elseif ('host_with_path_by_locale' === $multisite) {
                $container->setAlias('sonata.page.site.selector', 'sonata.page.site.selector.host_with_path_by_locale');

                $container->removeDefinition('sonata.page.site.selector.host_with_path');
                $container->removeDefinition('sonata.page.site.selector.host');
                $container->removeDefinition('sonata.page.site.selector.host_by_locale');
            }
        }
    }

    /**
     * Configure the page templates.
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Array of configuration
     */
    public function configureTemplates(ContainerBuilder $container, array $config)
    {
        $templateManager = $container->getDefinition('sonata.page.template_manager');

        // add all templates to manager
        $definitions = [];
        foreach ($config['templates'] as $code => $info) {
            $definition = new Definition('Sonata\PageBundle\Model\Template', [
                $info['name'],
                $info['path'],
                $info['containers'],
            ]);

            $definition->setPublic(false);
            $definitions[$code] = $definition;
        }

        $templateManager->addMethodCall('setAll', [$definitions]);

        // set default template
        $templateManager->addMethodCall('setDefaultTemplateCode', [$config['default_template']]);
    }

    /**
     * Configure the page admin templates.
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Array of configuration
     */
    public function configureTemplatesAdmin(ContainerBuilder $container, array $config)
    {
        $templateManager = $container->getDefinition('sonata.page.admin.page');

        $definitions = $config['templates_admin'];

        $templateManager->addMethodCall('setTemplates', [$definitions]);
    }

    /**
     * Configure the cache options.
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Array of configuration
     */
    public function configureCache(ContainerBuilder $container, array $config)
    {
        if (isset($config['caches']['esi'])) {
            $container
                ->getDefinition('sonata.page.cache.esi')
                ->replaceArgument(0, $config['caches']['esi']['token'])
                ->replaceArgument(1, $config['caches']['esi']['servers'])
                ->replaceArgument(3, 3 === $config['caches']['esi']['version'] ? 'ban' : 'purge');
        } else {
            $container->removeDefinition('sonata.page.cache.esi');
        }

        if (isset($config['caches']['ssi'])) {
            $container
                ->getDefinition('sonata.page.cache.ssi')
                ->replaceArgument(0, $config['caches']['ssi']['token']);
        } else {
            $container->removeDefinition('sonata.page.cache.ssi');
        }
    }

    /**
     * Configures the page custom exceptions.
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    An array of bundle configuration
     */
    public function configureExceptions(ContainerBuilder $container, array $config)
    {
        $exceptions = [];
        foreach ($config['catch_exceptions'] as $keyWord => $codes) {
            foreach ($codes as $code) {
                $exceptions[$code] = sprintf('_page_internal_error_%s', $keyWord);
            }
        }

        // add exception pages in exception_listener
        $container->getDefinition('sonata.page.kernel.exception_listener')
            ->replaceArgument(6, $exceptions);

        // add exception pages as default rendering parameters in page templates
        $container->getDefinition('sonata.page.template_manager')
            ->replaceArgument(1, ['error_codes' => $exceptions]);
    }

    /**
     * Configures the page services.
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    An array of bundle configuration
     */
    public function configurePageServices(ContainerBuilder $container, array $config)
    {
        // set the default page service to use when no page type has been set. (backward compatibility)
        $definition = $container->getDefinition('sonata.page.page_service_manager');
        $definition->addMethodCall('setDefault', [new Reference($config['default_page_service'])]);
    }
}
