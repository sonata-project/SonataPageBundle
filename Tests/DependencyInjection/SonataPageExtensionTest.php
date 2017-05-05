<?php

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
    /**
     * Tests the configureClassesToCompile method.
     */
    public function testConfigureClassesToCompile()
    {
        $extension = new SonataPageExtension();
        $extension->configureClassesToCompile();

        $this->assertNotContains(
            'Sonata\\PageBundle\\Request\\SiteRequest',
            $extension->getClassesToCompile()
        );
        $this->assertNotContains(
            'Sonata\\PageBundle\\Request\\SiteRequestInterface',
            $extension->getClassesToCompile()
        );
    }

    public function testRequestContextServiceIsDefined()
    {
        $this->container->setParameter('kernel.bundles', array());
        $this->load();
        $this->assertContainerBuilderHasService('sonata.page.router.request_context');
    }

    public function testApiServicesAreDefinedWhenSpecificBundlesArePresent()
    {
        $this->container->setParameter('kernel.bundles', array(
            'FOSRestBundle' => 42,
            'NelmioApiDocBundle' => 42,
        ));
        $this->load();
        $this->assertContainerBuilderHasService('sonata.page.serializer.handler.page');
    }

    public function testAdminServicesAreDefinedWhenAdminBundlesIsPresent()
    {
        $this->container->setParameter('kernel.bundles', array(
            'SonataAdminBundle' => 42,
        ));
        $this->load();
        $this->assertContainerBuilderHasService('sonata.page.admin.page');
    }

    protected function getContainerExtensions()
    {
        return array(new SonataPageExtension());
    }

    protected function getMinimalConfiguration()
    {
        return array(
            'multisite' => 'host',
            'default_template' => null,
            'templates' => null,
        );
    }
}
