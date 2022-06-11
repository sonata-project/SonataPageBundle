<?php

declare(strict_types=1);

namespace Sonata\PageBundle\DependencyInjection\Compiler;

use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CmsManagerServiceLocatorCompilerPass.
 *
 * @author Valentin Merlet <merlet.valentin@gmail.com>
 */
final class CmsManagerServiceLocatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $locatableServices = [];

        $cmsManagerServiceLocator = $container->findDefinition('sonata.cms_manager.service_locator');

        /** @var Definition $definition */
        foreach ($container->getDefinitions() as $definition) {
            $definitionClass = $definition->getClass();
            if (null === $definitionClass) {
                continue;
            }
            $interfaces = (new \ReflectionClass($definitionClass))->getInterfaces();

            if (\in_array(CmsManagerInterface::class, $interfaces)) {
                $locatableServices[$definition->innerServiceId] = new Reference($definition->innerServiceId);
            }
        }

        $cmsManagerServiceLocator->addArgument(ServiceLocatorTagPass::register($container, $locatableServices));
    }
}
