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

        // todo: use the configuration class
        $configs = call_user_func_array('array_merge_recursive', $configs);

        $manager = $container->getDefinition('sonata.page.manager');

        $manager->addMethodCall('setOptions', array($configs));

        foreach($configs['services'] as $id => $settings) {
            $cache = isset($settings['cache']) ? $settings['cache'] : 'sonata.page.cache.noop';

            $manager->addMethodCall('addCacheService', array($id, new Reference($cache)));
        }

        $invalidate = isset($configs['cache_invalidation']) ? $configs['cache_invalidation'] : 'sonata.page.cache.invalidation.simple';
        $manager->replaceArgument(3, new Reference($invalidate));

        if (isset($configs['caches'])) {
            if (isset($configs['caches']['sonata.page.cache.esi']['servers'])) {
                $servers = (array) $configs['caches']['sonata.page.cache.esi']['servers'];

                $cache = $container->getDefinition('sonata.page.cache.esi');
                $cache->replaceArgument(0, $servers);
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