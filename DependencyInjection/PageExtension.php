<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundle\Sonata\PageBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * PageExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PageExtension extends Extension {

    /**
     * Loads the url shortener configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad($config, ContainerBuilder $container) {

        // define the page manager
        $definition = new Definition($config['class']);
        $definition->addMethodCall('setContainer', array(new Reference('service_container')));
        $definition->addMethodCall('setOptions', array(isset($config['options']) ? $config['options'] : array()));
        $container->setDefinition('page.manager', $definition);


        // define the block service
        foreach($config['blocks'] as $block) {

            $definition = new Definition($block['class']);
            $definition->addMethodCall('setName', array($block['id']));
            $definition->addMethodCall('setContainer', array(new Reference('service_container')));

            $container->setDefinition(sprintf('page.block.%s', $block['id']), $definition);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath() {

        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace() {

        return 'http://www.sonata-project.org/schema/dic/page';
    }

    public function getAlias() {

        return "page";
    }
}