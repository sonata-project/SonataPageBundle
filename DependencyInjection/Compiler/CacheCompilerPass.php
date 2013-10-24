<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CacheCompilerPass
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CacheCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $services = array();

        foreach ($container->findTaggedServiceIds('sonata.page.manager') as $id => $attributes) {
            if (!$container->hasDefinition($id)) {
                continue;
            }

            $services[$attributes[0]['type']] = new Reference($id);
        }

        if ($container->hasDefinition('sonata.page.cache.esi')) {
            $container->getDefinition('sonata.page.cache.esi')->replaceArgument(6, $services);
        }

        if ($container->hasDefinition('sonata.page.cache.ssi')) {
            $container->getDefinition('sonata.page.cache.ssi')->replaceArgument(4, $services);
        }
    }
}
