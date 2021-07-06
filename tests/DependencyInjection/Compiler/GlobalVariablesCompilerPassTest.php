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

namespace Sonata\PageBundle\Tests\DependencyInjection\Compiler;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\PageBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Twig\Environment;

final class GlobalVariablesCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testLoadTwigGlobalVariables(): void
    {
        $twigDefinition = new Definition(Environment::class);

        $this->container
            ->setDefinition('twig', $twigDefinition);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'twig',
            'addGlobal',
            ['sonata_page', new Reference('sonata.page.twig.global')]
        );
    }

    public function testLoadsPageAdminTwigGlobalVariables(): void
    {
        $twigDefinition = new Definition(Environment::class);

        $this->container
            ->setDefinition('twig', $twigDefinition);

        $this->container
            ->register('sonata.page.admin.page', $this->createStub(AdminInterface::class));

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'twig',
            'addGlobal',
            ['sonata_page_admin', new Reference('sonata.page.admin.page')]
        );
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
    }
}
