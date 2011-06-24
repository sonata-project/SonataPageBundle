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
        $loader->load('validator.xml');

        // todo: use the configuration class
        $configs = call_user_func_array('array_merge_recursive', $configs);

        $cmsPage = $container->getDefinition('sonata.page.cms.page');
        $cmsSnapshot = $container->getDefinition('sonata.page.cms.snapshot');

        $cmsPage->addMethodCall('setOptions', array($configs));
        $cmsSnapshot->addMethodCall('setOptions', array($configs));

        foreach($configs['services'] as $id => $settings) {
            $cache = isset($settings['cache']) ? $settings['cache'] : 'sonata.page.cache.noop';

            $cmsPage->addMethodCall('addCacheService', array($id, new Reference($cache)));
            $cmsSnapshot->addMethodCall('addCacheService', array($id, new Reference($cache)));
        }

        $invalidate = isset($configs['cache_invalidation']) ? $configs['cache_invalidation'] : 'sonata.page.cache.invalidation.simple';
        $cmsPage->replaceArgument(3, new Reference($invalidate));
        $cmsSnapshot->replaceArgument(2, new Reference($invalidate));

        $this->configureCache($container, $configs);
    }

    public function configureCache(ContainerBuilder $container, $configs)
    {
         if (isset($configs['caches'])) {
            if (isset($configs['caches']['sonata.page.cache.esi']['servers'])) {
                $servers = (array) $configs['caches']['sonata.page.cache.esi']['servers'];

                $cache = $container->getDefinition('sonata.page.cache.esi');
                $cache->replaceArgument(0, $servers);
            }

            if (isset($configs['caches']['sonata.page.cache.mongo'])) {
                $settings = $configs['caches']['sonata.page.cache.mongo'];

                $servers    = isset($settings['servers']) ? (array) $settings['servers'] : array();
                $database   = isset($settings['database']) ? $settings['database'] : '';
                $collection = isset($settings['collection']) ? $settings['collection'] : '';

                $cache = $container->getDefinition('sonata.page.cache.mongo');
                $cache->replaceArgument(0, $servers);
                $cache->replaceArgument(1, $database);
                $cache->replaceArgument(2, $collection);
            }

            if (isset($configs['caches']['sonata.page.cache.memcached'])) {
                $settings = $configs['caches']['sonata.page.cache.memcached'];

                $prefix    = isset($settings['prefix']) ? (array) $settings['prefix'] : uniqid();
                $servers   = isset($settings['servers']) ? (array) $settings['servers'] : array();

                $cache = $container->getDefinition('sonata.page.cache.memcached');
                $cache->replaceArgument(0, $prefix);
                $cache->replaceArgument(1, $servers);
            }

            if (isset($configs['caches']['sonata.page.cache.apc'])) {
                $settings = $configs['caches']['sonata.page.cache.apc'];

                $token     = isset($settings['token']) ? $settings['token'] : 'changeme';
                $prefix    = isset($settings['prefix']) ? $settings['prefix'] : uniqid();
                $servers   = isset($settings['servers']) ? (array) $settings['servers'] : array();

                $cache = $container->getDefinition('sonata.page.cache.apc');
                $cache->replaceArgument(1, $token);
                $cache->replaceArgument(2, $prefix);
                $cache->replaceArgument(3, $servers);
            }
        }
    }
    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.sonata-project.org/schema/dic/page';
    }

    public function getAlias()
    {
        return "sonata_page";
    }
}