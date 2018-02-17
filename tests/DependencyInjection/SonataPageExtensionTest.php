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

namespace Sonata\PageBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sonata\PageBundle\DependencyInjection\SonataPageExtension;

/**
 * Tests the SonataPageExtension.
 *
 * @author Rémi Marseille <marseille@ekino.com>
 */
class SonataPageExtensionTest extends AbstractExtensionTestCase
{
    public function testRequestContextServiceIsDefined(): void
    {
        $this->container->setParameter('kernel.bundles', []);
        $this->load();
        $this->assertContainerBuilderHasService('sonata.page.router.request_context');
    }

    public function testApiServicesAreDefinedWhenSpecificBundlesArePresent(): void
    {
        $this->container->setParameter('kernel.bundles', [
            'FOSRestBundle' => 42,
            'NelmioApiDocBundle' => 42,
        ]);
        $this->load();
        $this->assertContainerBuilderHasService('sonata.page.serializer.handler.page');
    }

    public function testAdminServicesAreDefinedWhenAdminBundlesIsPresent(): void
    {
        $this->container->setParameter('kernel.bundles', [
            'SonataAdminBundle' => 42,
        ]);
        $this->load();
        $this->assertContainerBuilderHasService('sonata.page.admin.page');
    }

    public function testRouterAutoRegister(): void
    {
        $this->container->setParameter('kernel.bundles', [
            'CmfRouterBundle' => 42,
        ]);
        $this->load([
            'router_auto_register' => [
                'enabled' => true,
                'priority' => 84,
            ],
        ]);
        $this->assertContainerBuilderHasParameter('sonata.page.router_auto_register.enabled', true);
        $this->assertContainerBuilderHasParameter('sonata.page.router_auto_register.priority', 84);
    }

    protected function getContainerExtensions()
    {
        return [new SonataPageExtension()];
    }

    protected function getMinimalConfiguration()
    {
        return [
            'multisite' => 'host',
            'default_template' => null,
            'templates' => null,
        ];
    }
}
