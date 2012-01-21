<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataPageExtension extends Extension
{
    /**
     * Loads the url shortener configuration.
     *
     * @param array            $configs    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('page.xml');
        $loader->load('admin.xml');
        $loader->load('block.xml');
        $loader->load('orm.xml');
        $loader->load('form.xml');
        $loader->load('cache.xml');
        $loader->load('twig.xml');
        $loader->load('http_kernel.xml');

        $this->configureMultisite($container, $config);
        $this->configureInvalidation($container, $config);
        $this->configureCache($container, $config);
        $this->configureTemplate($container, $config);
        $this->configureExceptions($container, $config);

        $cmsPage = $container->getDefinition('sonata.page.cms.page');
        $cmsSnapshot = $container->getDefinition('sonata.page.cms.snapshot');

        $cmsPage->addMethodCall('setOptions', array($config));
        $cmsSnapshot->addMethodCall('setOptions', array($config));

        foreach ($config['services'] as $id => $settings) {
            $cmsPage->addMethodCall('addCacheService', array($id, new Reference($settings['cache'])));
            $cmsSnapshot->addMethodCall('addCacheService', array($id, new Reference($settings['cache'])));
        }

        $this->addClassesToCompile(array(
            'Sonata\\PageBundle\\Request\\SiteRequest'
        ));

        $this->registerDoctrineMapping($config);
        $this->registerParameters($container, $config);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $config
     * @return void
     */
    public function registerParameters(ContainerBuilder $container, $config)
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
     * @param array $config
     * @return void
     */
    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['class']['page'], 'mapOneToMany', array(
            'fieldName'     => 'children',
            'targetEntity'  => $config['class']['page'],
            'cascade'       => array(
                'remove',
                'persist',
                'refresh',
                'merge',
                'detach',
             ),
            'mappedBy'      => 'parent',
            'orphanRemoval' => false,
            'orderBy'       => array(
                'position'  => 'ASC',
            ),
        ));

        $collector->addAssociation($config['class']['page'], 'mapOneToMany', array(
            'fieldName'     => 'blocks',
            'targetEntity'  => $config['class']['block'],
            'cascade' => array(
                'remove',
                'persist',
                'refresh',
                'merge',
                'detach',
            ),
            'mappedBy'      => 'page',
            'orphanRemoval' => false,
            'orderBy'       => array(
                'position'  => 'ASC',
            ),
        ));

        $collector->addAssociation($config['class']['page'], 'mapOneToOne', array(
            'fieldName'     => 'site',
            'targetEntity'  => $config['class']['site'],
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => NULL,
            'joinColumns'   => array(
                array(
                    'name'  => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['page'], 'mapOneToOne', array(
            'fieldName'     => 'parent',
            'targetEntity'  => $config['class']['page'],
            'cascade'       => array(
                 'persist',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => NULL,
            'joinColumns'   => array(
                array(
                    'name'  => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['page'], 'mapOneToMany', array(
             'fieldName' => 'sources',
             'targetEntity' => $config['class']['page'],
             'cascade' => array(
                 'remove',
                 'persist',
                 'refresh',
                 'merge',
                 'detach',
             ),
             'mappedBy' => 'target',
             'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['page'], 'mapOneToOne', array(
            'fieldName' => 'target',
            'targetEntity' => $config['class']['page'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => NULL,
            'inversedBy' => NULL,
            'joinColumns' => array(
                array(
                    'name' => 'target_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['block'], 'mapOneToMany', array(
            'fieldName' => 'children',
            'targetEntity' => $config['class']['block'],
            'cascade' => array(
                'remove',
                'persist',
            ),
            'mappedBy' => 'parent',
            'orphanRemoval' => true,
            'orderBy' => array(
                'position' => 'ASC',
            ),
        ));

        $collector->addAssociation($config['class']['block'], 'mapOneToOne', array(
            'fieldName' => 'parent',
            'targetEntity' => $config['class']['block'],
            'cascade' => array(
            ),
            'mappedBy' => NULL,
            'inversedBy' => NULL,
            'joinColumns' => array(
                array(
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['block'], 'mapOneToOne', array(
            'fieldName' => 'page',
            'targetEntity' => $config['class']['page'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => NULL,
            'inversedBy' => NULL,
            'joinColumns' => array(
                array(
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['snapshot'], 'mapOneToOne', array(
            'fieldName'     => 'site',
            'targetEntity'  => $config['class']['site'],
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => NULL,
            'joinColumns'   => array(
                array(
                    'name'      => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete'  => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['snapshot'], 'mapOneToOne', array(
            'fieldName'     => 'page',
            'targetEntity'  => $config['class']['page'],
            'cascade' => array(
                'remove',
                'persist',
                'refresh',
                'merge',
                'detach',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => NULL,
            'joinColumns'   => array(
                array(
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $config
     * @return void
     */
    public function configureMultisite(ContainerBuilder $container, $config)
    {
        /**
         * The multipath option required a specific router and RequestContext
         */
        if ($config['multisite'] == 'domain_with_path') {
            $container->setAlias('router', 'sonata.page.router.default');
            $container->setAlias('sonata.page.site.selector', 'sonata.page.site.selector.domain_with_path');

            $container->removeDefinition('sonata.page.site.selector.domain');
        } else {
            $container->setAlias('sonata.page.site.selector', 'sonata.page.site.selector.domain');

            $container->removeDefinition('sonata.page.router.default');
            $container->removeDefinition('sonata.page.site.selector.domain_with_path');
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $config
     * @return void
     */
    public function configureTemplate(ContainerBuilder $container, $config)
    {
        $pageManager = $container->getDefinition('sonata.page.manager.page');
        $snapshotManager = $container->getDefinition('sonata.page.manager.snapshot');

        $definitions = array();
        foreach ($config['templates'] as $code => $info) {
            $definition = new Definition('Sonata\PageBundle\Model\Template', array(
                $info['name'],
                $info['path'],
            ));

            $definition->setPublic(false);
            $definitions[$code] = $definition;
        }

        $pageManager->addMethodCall('setTemplates', array($definitions));
        $snapshotManager->addMethodCall('setTemplates', array($definitions));

        $pageManager->addMethodCall('setDefaultTemplateCode', array($config['default_template']));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $config
     * @return void
     */
    public function configureInvalidation(ContainerBuilder $container, $config)
    {
        $cmsPage = $container->getDefinition('sonata.page.cms.page');
        $cmsSnapshot = $container->getDefinition('sonata.page.cms.snapshot');

        $cmsPage->replaceArgument(1, new Reference($config['cache_invalidation']['service']));
        $cmsSnapshot->replaceArgument(1, new Reference($config['cache_invalidation']['service']));

        $recorder = $container->getDefinition('sonata.page.cache.model_identifier');
        foreach ($config['cache_invalidation']['classes'] as $class => $method) {
            $recorder->addMethodCall('addClass', array($class, $method));
        }

        $cmsPage->addMethodCall('setRecorder', array(new Reference($config['cache_invalidation']['recorder'])));
        $cmsSnapshot->addMethodCall('setRecorder', array(new Reference($config['cache_invalidation']['recorder'])));
    }

    /**
     * @throws \RuntimeException
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $config
     * @return void
     */
    public function configureCache(ContainerBuilder $container, $config)
    {
        if (isset($config['caches']['esi'])) {
            $container
                ->getDefinition('sonata.page.cache.esi')
                ->replaceArgument(0, $config['caches']['esi']['servers'])
            ;
        } else {
            $container->removeDefinition('sonata.page.cache.esi');
        }

        if (isset($config['caches']['mongo'])) {
            if (!class_exists('\Mongo', true)) {
                throw new \RuntimeException(<<<HELP
The `sonata.page.cache.mongo` service is configured, however the Mongo class is not available.

To resolve this issue, please install the related library : http://php.net/manual/en/book.mongo.php
or remove the mongo cache settings from the configuration file.
HELP
                );
            }

            $servers = array();
            foreach ($config['caches']['mongo']['servers'] as $server) {
                if ($server['user']) {
                    $servers[] = sprintf('%s:%s@%s:%s', $server['user'], $server['password'], $server['host'], $server['port']);
                } else {
                    $servers[] = sprintf('%s:%s', $server['host'], $server['port']);
                }
            }

            $container
                ->getDefinition('sonata.page.cache.mongo')
                ->replaceArgument(0, $servers)
                ->replaceArgument(1, $config['caches']['mongo']['database'])
                ->replaceArgument(2, $config['caches']['mongo']['collection'])
            ;
        } else {
            $container->removeDefinition('sonata.page.cache.mongo');
        }

        if (isset($configs['caches']['memcached'])) {

            if (!class_exists('\Memcached', true)) {
                throw new \RuntimeException(<<<HELP
The `sonata.page.cache.memcached` service is configured, however the Memcached class is not available.

To resolve this issue, please install the related library : http://php.net/manual/en/book.memcached.php
or remove the memcached cache settings from the configuration file.
HELP
                );
            }

            $container
                ->getDefinition('sonata.page.cache.memcached')
                ->replaceArgument(0, $config['caches']['memcached']['prefix'])
                ->replaceArgument(1, $config['caches']['memcached']['servers'])
            ;
        } else {
            $container->removeDefinition('sonata.page.cache.memcached');
        }

        if (isset($configs['caches']['apc'])) {

            if (!function_exists('apc_fetch')) {
                throw new \RuntimeException(<<<HELP
The `sonata.page.cache.apc` service is configured, however the apc_* functions are not available.

To resolve this issue, please install the related library : http://php.net/manual/en/book.apc.php
or remove the APC cache settings from the configuration file.
HELP
                );
            }

            $container
                ->getDefinition('sonata.page.cache.apc')
                ->replaceArgument(1, $config['caches']['apc']['token'])
                ->replaceArgument(2, $config['caches']['apc']['prefix'])
                ->replaceArgument(3, $config['caches']['apc']['servers'])
            ;
        } else {
            $container->removeDefinition('sonata.page.cache.apc');
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $config
     * @return void
     */
    public function configureExceptions(ContainerBuilder $container, $config)
    {
        $cmsPage = $container->getDefinition('sonata.page.cms.page');
        $cmsSnapshot = $container->getDefinition('sonata.page.cms.snapshot');

        $exceptions = array();
        foreach ($config['catch_exceptions'] as $keyWord => $codes) {
            foreach ($codes as $code) {
                $exceptions[$code] = sprintf('_page_internal_error_%s', $keyWord);
            }
        }

        $cmsPage->replaceArgument(3, $exceptions);
        $cmsSnapshot->replaceArgument(3, $exceptions);
    }
}