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
 * Link the block service to the Page Manager
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TweakCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $cmsPage      = $container->getDefinition('sonata.page.cms.page');
        $snapshotPage = $container->getDefinition('sonata.page.cms.snapshot');

        foreach ($container->findTaggedServiceIds('sonata.page.block') as $id => $attributes) {
            $cmsPage->addMethodCall('addBlockService', array($id, new Reference($id)));
            $snapshotPage->addMethodCall('addBlockService', array($id, new Reference($id)));
        }

        if ($container->hasDefinition('sonata.page.orm.event_subscriber.default')) {
            $ormListener = $container->getDefinition('sonata.page.orm.event_subscriber.default');
            foreach ($container->findTaggedServiceIds('sonata.page.cache') as $id => $attributes) {
                if (!$container->hasDefinition($id)) {
                    continue;
                }

                $ormListener->addMethodCall('addCache', array(new Reference($id)));
            }
        }
    }
}
