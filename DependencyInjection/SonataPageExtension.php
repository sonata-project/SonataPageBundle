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
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
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

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('page.xml');
        $loader->load('admin.xml');
        $loader->load('block.xml');
        $loader->load('orm.xml');
        $loader->load('form.xml');
        $loader->load('cache.xml');
        $loader->load('twig.xml');
        $loader->load('http_kernel.xml');
        $loader->load('consumer.xml');

        $this->configureMultisite($container, $config);
        $this->configureCache($container, $config);
        $this->configureTemplates($container, $config);
        $this->configureExceptions($container, $config);
        $this->configurePageDefaults($container, $config);
        $this->configurePageServices($container, $config);

        $this->addClassesToCompile(array(
            'Sonata\\PageBundle\\Request\\SiteRequest'
        ));

        $container->getDefinition('sonata.page.decorator_strategy')
            ->replaceArgument(0, $config['ignore_routes'])
            ->replaceArgument(1, $config['ignore_route_patterns'])
            ->replaceArgument(2, $config['ignore_uri_patterns']);

        //Set the entity manager that should be used to store pages:
        $container->setAlias('sonata.page.entity_manager', $config['entity_manager']);

        $this->registerDoctrineMapping($config);
        $this->registerParameters($container, $config);
    }

    /**
     * Configure the page default settings
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Array of configuration
     */
    public function configurePageDefaults(ContainerBuilder $container, array $config)
    {
        $defaults = array(
            'templateCode'  => $config['default_template'],
            'enabled'       => true,
            'routeName'     => null,
            'name'          => null,
            'slug'          => null,
            'url'           => null,
            'requestMethod' => null,
            'decorate'      => true,
        );

        $container->getDefinition('sonata.page.manager.page')
            ->replaceArgument(2, $defaults);


        foreach ($config['page_defaults'] as $name => $pageDefaults) {
            $config['page_defaults'][$name] = array_merge($defaults, $pageDefaults);
        }

        $container->getDefinition('sonata.page.manager.page')
            ->replaceArgument(3, $config['page_defaults']);
    }

    /**
     * Registers service parameters from bundle configuration
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
     * Registers doctrine mapping on concrete page entities
     *
     * @param array $config
     */
    public function registerDoctrineMapping(array $config)
    {
        if (!class_exists($config['class']['page'])) {
            return;
        }

        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['class']['page'], 'mapOneToMany', array(
            'fieldName'     => 'children',
            'targetEntity'  => $config['class']['page'],
            'cascade'       => array(
                'persist',
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

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', array(
            'fieldName'     => 'site',
            'targetEntity'  => $config['class']['site'],
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => null,
            'inversedBy'    => null,
            'joinColumns'   => array(
                array(
                    'name'  => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', array(
            'fieldName'     => 'parent',
            'targetEntity'  => $config['class']['page'],
            'cascade'       => array(
                 'persist',
            ),
            'mappedBy'      => null,
            'inversedBy'    => null,
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
             'cascade' => array(),
             'mappedBy' => 'target',
             'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', array(
            'fieldName' => 'target',
            'targetEntity' => $config['class']['page'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => null,
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

        $collector->addAssociation($config['class']['block'], 'mapManyToOne', array(
            'fieldName' => 'parent',
            'targetEntity' => $config['class']['block'],
            'cascade' => array(
            ),
            'mappedBy' => null,
            'inversedBy' => null,
            'joinColumns' => array(
                array(
                    'name' => 'parent_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['block'], 'mapManyToOne', array(
            'fieldName' => 'page',
            'targetEntity' => $config['class']['page'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy' => null,
            'inversedBy' => null,
            'joinColumns' => array(
                array(
                    'name' => 'page_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['snapshot'], 'mapManyToOne', array(
            'fieldName'     => 'site',
            'targetEntity'  => $config['class']['site'],
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => null,
            'inversedBy'    => null,
            'joinColumns'   => array(
                array(
                    'name'      => 'site_id',
                    'referencedColumnName' => 'id',
                    'onDelete'  => 'CASCADE',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['snapshot'], 'mapManyToOne', array(
            'fieldName'     => 'page',
            'targetEntity'  => $config['class']['page'],
            'cascade' => array(
                'persist',
            ),
            'mappedBy'      => null,
            'inversedBy'    => null,
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
     * Configure the multi-site feature
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Array of configuration
     */
    public function configureMultisite(ContainerBuilder $container, $config)
    {
        /**
         * The multipath option required a specific router and RequestContext
         */
        if ($config['multisite'] == 'host_with_path') {
            $container->setAlias('router', 'sonata.page.router.default');
            $container->setAlias('sonata.page.site.selector', 'sonata.page.site.selector.host_with_path');

            $container->removeDefinition('sonata.page.site.selector.host');
        } else {
            $container->setAlias('sonata.page.site.selector', 'sonata.page.site.selector.host');

            $container->removeDefinition('sonata.page.router.default');
            $container->removeDefinition('sonata.page.site.selector.host_with_path');
        }
    }

    /**
     * Configure the page templates
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    Array of configuration
     */
    public function configureTemplates(ContainerBuilder $container, $config)
    {
        $templateManager = $container->getDefinition('sonata.page.template_manager');

        // inject stream option into template manager
        $templateManager->replaceArgument(2, $config['use_streamed_response']);

        // add all templates to manager
        $definitions = array();
        foreach ($config['templates'] as $code => $info) {
            $definition = new Definition('Sonata\PageBundle\Model\Template', array(
                $info['name'],
                $info['path'],
            ));

            $definition->setPublic(false);
            $definitions[$code] = $definition;
        }
        $templateManager->addMethodCall('setAll', array($definitions));

        // set default template
        $templateManager->addMethodCall('setDefaultTemplateCode', array($config['default_template']));
    }

    /**
     * Configure the cache options
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
                ->replaceArgument(1, $config['caches']['esi']['servers']);
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
     * Configures the page custom exceptions
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    An array of bundle configuration
     */
    public function configureExceptions(ContainerBuilder $container, $config)
    {
        $exceptions = array();
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
            ->replaceArgument(1, array('error_codes' => $exceptions));
    }

    /**
     * Configures the page services
     *
     * @param ContainerBuilder $container Container builder
     * @param array            $config    An array of bundle configuration
     */
    public function configurePageServices(ContainerBuilder $container, $config)
    {
        // set the default page service to use when no page type has been set. (backward compatibility)
        $definition = $container->getDefinition('sonata.page.page_service_manager');
        $definition->addMethodCall('setDefault', array(new Reference($config['default_page_service'])));
    }
}