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
        $this->configureCache($container, $config);
        $this->configureTemplate($container, $config);
        $this->configureExceptions($container, $config);

        $this->addClassesToCompile(array(
            'Sonata\\PageBundle\\Request\\SiteRequest'
        ));

        $container->getDefinition('sonata.page.decorator_strategy')
            ->replaceArgument(0, $config['ignore_routes'])
            ->replaceArgument(1, $config['ignore_route_patterns'])
            ->replaceArgument(2, $config['ignore_uri_patterns'])
        ;

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
        if (!class_exists($config['class']['page'])) {
            return;
        }

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

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', array(
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

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', array(
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

        $collector->addAssociation($config['class']['page'], 'mapManyToOne', array(
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

        $collector->addAssociation($config['class']['block'], 'mapManyToOne', array(
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

        $collector->addAssociation($config['class']['block'], 'mapManyToOne', array(
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

        $collector->addAssociation($config['class']['snapshot'], 'mapManyToOne', array(
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

        $collector->addAssociation($config['class']['snapshot'], 'mapManyToOne', array(
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
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $config
     * @return void
     */
    public function configureTemplate(ContainerBuilder $container, $config)
    {
        $renderer = $container->getDefinition('sonata.page.renderer');

        $definitions = array();
        foreach ($config['templates'] as $code => $info) {
            $definition = new Definition('Sonata\PageBundle\Model\Template', array(
                $info['name'],
                $info['path'],
            ));

            $definition->setPublic(false);
            $definitions[$code] = $definition;
        }

        $renderer->replaceArgument(3, $definitions);
        $renderer->addMethodCall('setDefaultTemplateCode', array($config['default_template']));

        $container->getDefinition('sonata.page.manager.page')
            ->replaceArgument('2', array(
                'templateCode'  => $config['default_template'],
                'enabled'       => true,
                'routeName'     => null,
                'name'          => null,
                'slug'          => null,
                'url'           => null,
                'requestMethod' => null,
                'decorate'      => true,
            ));
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
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param $config
     * @return void
     */
    public function configureExceptions(ContainerBuilder $container, $config)
    {
        $exceptions = array();
        foreach ($config['catch_exceptions'] as $keyWord => $codes) {
            foreach ($codes as $code) {
                $exceptions[$code] = sprintf('_page_internal_error_%s', $keyWord);
            }
        }

        $container->getDefinition('sonata.page.kernel.exception_listener')
            ->replaceArgument(6, $exceptions)
        ;

        $container->getDefinition('sonata.page.renderer')
            ->replaceArgument(4, $exceptions)
        ;
    }
}