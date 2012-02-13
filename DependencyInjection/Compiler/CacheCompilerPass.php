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

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
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
        if (!$container->hasDefinition('sonata.page.cache.esi')) {
            return;
        }

        $services = array();

        foreach ($container->findTaggedServiceIds('sonata.page.manager') as $id => $attributes) {
            if (!$container->hasDefinition($id)) {
                continue;
            }

            $services[] = new Reference($id);
        }

        $container->getDefinition('sonata.page.cache.esi')->replaceArgument(3, $services);
    }
}
