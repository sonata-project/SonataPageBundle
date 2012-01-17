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
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('page.xml');
        $loader->load('admin.xml');
        $loader->load('block.xml');
        $loader->load('orm.xml');
        $loader->load('form.xml');
        $loader->load('cache.xml');
        $loader->load('twig.xml');
        $loader->load('http_kernel.xml');

        // todo: use the configuration class
        $configs = call_user_func_array('array_merge_recursive', $configs);

        $this->configureInvalidation($container, $configs);
        $this->configureCache($container, $configs);
        $this->configureTemplate($container, $configs);
        $this->configureExceptions($container, $configs);

        $cmsPage = $container->getDefinition('sonata.page.cms.page');
        $cmsSnapshot = $container->getDefinition('sonata.page.cms.snapshot');

        $cmsPage->addMethodCall('setOptions', array($configs));
        $cmsSnapshot->addMethodCall('setOptions', array($configs));

        if (isset($configs['services'])) {
            foreach ($configs['services'] as $id => $settings) {
                $cache = isset($settings['cache']) ? $settings['cache'] : 'sonata.page.cache.noop';

                $cmsPage->addMethodCall('addCacheService', array($id, new Reference($cache)));
                $cmsSnapshot->addMethodCall('addCacheService', array($id, new Reference($cache)));
            }
        }
    }

    public function configureTemplate(ContainerBuilder $container, $configs)
    {
        $pageManager = $container->getDefinition('sonata.page.manager.page');
        $snapshotManager = $container->getDefinition('sonata.page.manager.snapshot');

        $defaults = array(
            'name'   => 'n-a',
            'path'   => 'SonataPageBundle::layout.html.twig',
        );

        if (!isset($configs['default_template'])) {
            $configs['default_template'] = 'default';
        }

        if (!isset($configs['templates'])) {
            $configs['templates'] = array('default' => array_merge($defaults, array(
                'name' => 'default',
            )));
        }
        $definitions = array();
        foreach ($configs['templates'] as $code => $info) {
            $info = array_merge($defaults, $info);
            $definition = new Definition('Sonata\PageBundle\Model\Template');
            foreach ($defaults as $key => $value) {
                $method = 'set' . ucfirst($key);
                $definition->addMethodCall($method, array($info[$key]));
            }
            $definition->setPublic(false);
            $definitions[$code] = $definition;
        }

        $pageManager->addMethodCall('setTemplates', array($definitions));
        $snapshotManager->addMethodCall('setTemplates', array($definitions));

        $pageManager->addMethodCall('setDefaultTemplateCode', array($configs['default_template']));
    }

    public function configureInvalidation(ContainerBuilder $container, $configs)
    {
        $cmsPage = $container->getDefinition('sonata.page.cms.page');
        $cmsSnapshot = $container->getDefinition('sonata.page.cms.snapshot');

        $invalidate = isset($configs['cache_invalidation']['service']) ? $configs['cache_invalidation']['service'] : 'sonata.page.cache.invalidation.simple';
        $cmsPage->replaceArgument(1, new Reference($invalidate));
        $cmsSnapshot->replaceArgument(1, new Reference($invalidate));

        if (!isset($configs['cache_invalidation']['classes'])) {
            $configs['cache_invalidation']['classes'] = array();
        }

        $recorder = $container->getDefinition('sonata.page.cache.model_identifier');
        foreach ($configs['cache_invalidation']['classes'] as $information) {
            $recorder->addMethodCall('addClass', array($information[0], $information[1]));
        }

        $recorder = isset($configs['cache_invalidation']['recorder']) ? $configs['cache_invalidation']['recorder'] : 'sonata.page.cache.recorder';
        $cmsPage->addMethodCall('setRecorder', array(new Reference($recorder)));
        $cmsSnapshot->addMethodCall('setRecorder', array(new Reference($recorder)));
    }

    public function configureCache(ContainerBuilder $container, $configs)
    {
        if (!isset($configs['caches'])) {
            $container->removeDefinition('sonata.page.cache.esi');
            $container->removeDefinition('sonata.page.cache.mongo');
            $container->removeDefinition('sonata.page.cache.apc');
            $container->removeDefinition('sonata.page.cache.memcached');

            return;
        }

        if (isset($configs['caches']['sonata.page.cache.esi']['servers'])) {
            $servers = (array) $configs['caches']['sonata.page.cache.esi']['servers'];

            $cache = $container->getDefinition('sonata.page.cache.esi');
            $cache->replaceArgument(0, $servers);
        } else {
            $container->removeDefinition('sonata.page.cache.esi');
        }

        if (isset($configs['caches']['sonata.page.cache.mongo'])) {
            if (!class_exists('\Mongo', true)) {
                throw new \RuntimeException(<<<HELP
The `sonata.page.cache.mongo` service is configured, however the Mongo class is not available.

To resolve this issue, please install the related library : http://php.net/manual/en/book.mongo.php
or remove the mongo cache settings from the configuration file.
HELP
                );
            }

            $settings = $configs['caches']['sonata.page.cache.mongo'];

            $servers    = isset($settings['servers']) ? (array) $settings['servers'] : array();
            $database   = isset($settings['database']) ? $settings['database'] : '';
            $collection = isset($settings['collection']) ? $settings['collection'] : '';

            $cache = $container->getDefinition('sonata.page.cache.mongo');
            $cache->replaceArgument(0, $servers);
            $cache->replaceArgument(1, $database);
            $cache->replaceArgument(2, $collection);
        } else {
            $container->removeDefinition('sonata.page.cache.mongo');
        }

        if (isset($configs['caches']['sonata.page.cache.memcached'])) {

            if (!class_exists('\Memcached', true)) {
                throw new \RuntimeException(<<<HELP
The `sonata.page.cache.memcached` service is configured, however the Memcached class is not available.

To resolve this issue, please install the related library : http://php.net/manual/en/book.memcached.php
or remove the memcached cache settings from the configuration file.
HELP
                );
            }

            $settings = $configs['caches']['sonata.page.cache.memcached'];

            $prefix    = isset($settings['prefix']) ? (array) $settings['prefix'] : uniqid();
            $servers   = isset($settings['servers']) ? (array) $settings['servers'] : array();

            $cache = $container->getDefinition('sonata.page.cache.memcached');
            $cache->replaceArgument(0, $prefix);
            $cache->replaceArgument(1, $servers);
        } else {
            $container->removeDefinition('sonata.page.cache.memcached');
        }

        if (isset($configs['caches']['sonata.page.cache.apc'])) {

            if (!function_exists('apc_fetch')) {
                throw new \RuntimeException(<<<HELP
The `sonata.page.cache.apc` service is configured, however the apc_* functions are not available.

To resolve this issue, please install the related library : http://php.net/manual/en/book.apc.php
or remove the APC cache settings from the configuration file.
HELP
                );
            }

            $settings = $configs['caches']['sonata.page.cache.apc'];

            $token     = isset($settings['token']) ? $settings['token'] : 'changeme';
            $prefix    = isset($settings['prefix']) ? $settings['prefix'] : uniqid();
            $servers   = isset($settings['servers']) ? (array) $settings['servers'] : array();

            $cache = $container->getDefinition('sonata.page.cache.apc');
            $cache->replaceArgument(1, $token);
            $cache->replaceArgument(2, $prefix);
            $cache->replaceArgument(3, $servers);
        } else {
            $container->removeDefinition('sonata.page.cache.apc');
        }
    }

    public function configureExceptions(ContainerBuilder $container, $configs)
    {
        $cmsPage = $container->getDefinition('sonata.page.cms.page');
        $cmsSnapshot = $container->getDefinition('sonata.page.cms.snapshot');

        $config = isset($configs['catch_exceptions']) ? $configs['catch_exceptions'] : array();
        $exceptions = array();

        foreach ($config as $keyWord => $codes) {
            if (is_array($codes)) {
                foreach ($codes as $code) {
                    $exceptions[$code] = sprintf('_page_internal_error_%s', $keyWord);
                }
            } else {
                $exceptions[$codes] = sprintf('_page_internal_error_%s', $keyWord);
            }
        }

        $cmsPage->replaceArgument(3, $exceptions);
        $cmsSnapshot->replaceArgument(3, $exceptions);
    }
}