<?php

declare(strict_types=1);

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

/**
 * @author Olivier Paradis <paradis@ekino.com>
 */
final class PageServiceCompilerPass implements CompilerPassInterface
{
    private string $manager = 'sonata.page.page_service_manager';

    private string $tagName = 'sonata.page';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition($this->manager)) {
            return;
        }

        $definition = $container->getDefinition($this->manager);

        $taggedServices = $container->findTaggedServiceIds($this->tagName);
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('add', [$id, new Reference($id)]);
        }
    }
}
