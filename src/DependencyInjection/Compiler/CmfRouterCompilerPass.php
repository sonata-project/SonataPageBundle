<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CmfRouterCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $enabled = 'sonata.page.router_auto_register.enabled';
        if ($container->hasParameter($enabled) && $container->getParameter($enabled)) {
            $container
                ->getDefinition('cmf_routing.router')
                ->addMethodCall('add', [
                    new Reference('sonata.page.router'),
                    $container->getParameter('sonata.page.router_auto_register.priority'),
                ]);
        }
    }
}
