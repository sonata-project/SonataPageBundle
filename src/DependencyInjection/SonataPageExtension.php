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

namespace Sonata\PageBundle\DependencyInjection;

use Sonata\Doctrine\Mapper\Builder\OptionsBuilder;
use Sonata\Doctrine\Mapper\DoctrineCollector;
use Sonata\PageBundle\Model\Template;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * NEXT_MAJOR: stop implementing PrependExtensionInterface
 */
final class SonataPageExtension extends Extension implements PrependExtensionInterface
{
    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function prepend(ContainerBuilder $container): void
    {
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $bundles = $container->getParameter('kernel.bundles');
        \assert(\is_array($bundles));

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('page.php');

        if (isset($bundles['SonataAdminBundle'])) {
            $loader->load('admin.php');
            $loader->load('controllers.php');

            if (!$config['direct_publication']) {
                $container->removeDefinition('sonata.page.admin.extension.snapshot');
            }

            $this->configureTemplatesAdmin($container, $config);
        }

        $loader->load('block.php');
        $loader->load('orm.php');
        $loader->load('form.php');
        $loader->load('twig.php');
        $loader->load('http_kernel.php');
        $loader->load('service.php');
        $loader->load('validators.php');
        $loader->load('command.php');
        $loader->load('slugify.php');

        $this->configureMultisite($container, $config);
        $this->configureTemplates($container, $config);
        $this->configureExceptions($container, $config);
        $this->configurePageDefaults($container, $config);
        $this->configurePageServices($container, $config);

        $container->setParameter('sonata.page.skip_redirection', $config['skip_redirection']);
        $container->setParameter('sonata.page.hide_disabled_blocks', $config['hide_disabled_blocks']);
        $container->getDefinition('sonata.page.decorator_strategy')
            ->replaceArgument(0, $config['ignore_routes'])
            ->replaceArgument(1, $config['ignore_route_patterns'])
            ->replaceArgument(2, $config['ignore_uri_patterns']);

        if (isset($bundles['SonataDoctrineBundle'])) {
            $this->registerSonataDoctrineMapping($config);
        } else {
            throw new \RuntimeException('You must register SonataDoctrineBundle to use SonataPageBundle.');
        }

        $this->registerParameters($container, $config);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configurePageDefaults(ContainerBuilder $container, array $config): void
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
            ->replaceArgument(3, $defaults);

        foreach ($config['page_defaults'] as $name => $pageDefaults) {
            $config['page_defaults'][$name] = array_merge($defaults, $pageDefaults);
        }

        $container->getDefinition('sonata.page.manager.page')
            ->replaceArgument(4, $config['page_defaults']);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function registerParameters(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('sonata.page.site.class', $config['class']['site']);
        $container->setParameter('sonata.page.block.class', $config['class']['block']);
        $container->setParameter('sonata.page.snapshot.class', $config['class']['snapshot']);
        $container->setParameter('sonata.page.page.class', $config['class']['page']);

        $container->setParameter('sonata.page.admin.site.entity', $config['class']['site']);
        $container->setParameter('sonata.page.admin.block.entity', $config['class']['block']);
        $container->setParameter('sonata.page.admin.snapshot.entity', $config['class']['snapshot']);
        $container->setParameter('sonata.page.admin.page.entity', $config['class']['page']);

        $container->setParameter('sonata.page.router_auto_register.enabled', $config['router_auto_register']['enabled']);
        $container->setParameter('sonata.page.router_auto_register.priority', $config['router_auto_register']['priority']);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureMultisite(ContainerBuilder $container, array $config): void
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

        if ($container->hasAlias('sonata.page.site.selector')) {
            $container->getAlias('sonata.page.site.selector')->setPublic(true);
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureTemplates(ContainerBuilder $container, array $config): void
    {
        $templateManager = $container->getDefinition('sonata.page.template_manager');

        // add all templates to manager
        $definitions = [];
        foreach ($config['templates'] as $code => $info) {
            $definition = new Definition(Template::class, [
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
     * @param array<string, mixed> $config
     */
    private function configureTemplatesAdmin(ContainerBuilder $container, array $config): void
    {
        $templateManager = $container->getDefinition('sonata.page.admin.page');

        $definitions = $config['templates_admin'];

        $templateManager->addMethodCall('setTemplates', [$definitions]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function configureExceptions(ContainerBuilder $container, array $config): void
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
     * @param array<string, mixed> $config
     */
    private function configurePageServices(ContainerBuilder $container, array $config): void
    {
        // set the default page service to use when no page type has been set. (backward compatibility)
        $definition = $container->getDefinition('sonata.page.page_service_manager');
        $definition->addMethodCall('setDefault', [new Reference($config['default_page_service'])]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function registerSonataDoctrineMapping(array $config): void
    {
        if (!class_exists($config['class']['page'])) {
            return;
        }

        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation(
            $config['class']['page'],
            'mapOneToMany',
            OptionsBuilder::createOneToMany('children', $config['class']['page'])
                ->cascade(['persist'])
                ->mappedBy('parent')
                ->addOrder('position', 'ASC')
        );

        $collector->addAssociation(
            $config['class']['page'],
            'mapOneToMany',
            OptionsBuilder::createOneToMany('blocks', $config['class']['block'])
                ->cascade(['remove', 'persist', 'refresh', 'detach'])
                ->mappedBy('page')
                ->addOrder('position', 'ASC')
        );

        $collector->addAssociation(
            $config['class']['page'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('site', $config['class']['site'])
                ->cascade(['persist'])
                ->addJoin([
                    'name' => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addAssociation(
            $config['class']['page'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('parent', $config['class']['page'])
                ->cascade(['persist'])
                ->inversedBy('children')
                ->addJoin([
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addAssociation(
            $config['class']['block'],
            'mapOneToMany',
            OptionsBuilder::createOneToMany('children', $config['class']['block'])
                ->cascade(['remove', 'persist'])
                ->mappedBy('parent')
                ->orphanRemoval()
                ->addOrder('position', 'ASC')
        );

        $collector->addAssociation(
            $config['class']['block'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('parent', $config['class']['block'])
                ->inversedBy('children')
                ->addJoin([
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addAssociation(
            $config['class']['block'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('page', $config['class']['page'])
                ->cascade(['persist'])
                ->inversedBy('blocks')
                ->addJoin([
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addAssociation(
            $config['class']['snapshot'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('site', $config['class']['site'])
                ->cascade(['persist'])
                ->addJoin([
                    'name' => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addAssociation(
            $config['class']['snapshot'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('page', $config['class']['page'])
                ->cascade(['persist'])
                ->addJoin([
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addIndex($config['class']['snapshot'], 'idx_snapshot_dates_enabled', [
            'publication_date_start',
            'publication_date_end',
            'enabled',
        ]);
    }
}
