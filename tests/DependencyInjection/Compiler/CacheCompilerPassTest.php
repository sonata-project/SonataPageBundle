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

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\PageBundle\Cache\BlockEsiCache;
use Sonata\PageBundle\Cache\BlockSsiCache;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\CmsManager\CmsSnapshotManager;
use Sonata\PageBundle\DependencyInjection\Compiler\CacheCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Routing\RouterInterface;

class CacheCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testCacheServicesInjection(): void
    {
        $cmsPage = $this->registerService('sonata.page.cms.page', CmsPageManager::class);
        $cmsPage->addTag('sonata.page.manager', ['type' => 'page']);

        $cmsSnapshot = $this->registerService('sonata.page.cms.snapshot', CmsSnapshotManager::class);
        $cmsSnapshot->addTag('sonata.page.manager', ['type' => 'snapshot']);

        $cacheEsi = $this->registerService('sonata.page.cache.esi', BlockEsiCache::class);
        $cacheEsi->setArguments([
            'token',
            [],
            $this->createMock(RouterInterface::class),
            'purgeInstruction',
            $this->createMock(ControllerResolverInterface::class),
            $this->createMock(ArgumentResolverInterface::class),
            $this->createMock(BlockRendererInterface::class),
            $this->createMock(BlockContextManagerInterface::class),
            [],
        ]);

        $cacheSsi = $this->registerService('sonata.page.cache.ssi', BlockSsiCache::class);
        $cacheSsi->setArguments([
            'token',
            $this->createMock(RouterInterface::class),
            $this->createMock(ControllerResolverInterface::class),
            $this->createMock(ArgumentResolverInterface::class),
            $this->createMock(BlockRendererInterface::class),
            $this->createMock(BlockContextManagerInterface::class),
            [],
        ]);

        $this->compile();

        $cacheEsi = $this->container->getDefinition('sonata.page.cache.esi');
        $cacheSsi = $this->container->getDefinition('sonata.page.cache.ssi');

        if ($this->container->has('sonata.page.cms.page') && $this->container->has('sonata.page.cms.snapshot')) {
            $this->assertSame($cacheEsi->getArguments()[8], $cacheSsi->getArguments()[6]);

            $services = $cacheEsi->getArguments()[8];

            $this->assertSame('sonata.page.cms.page', $services['page']->__toString());
            $this->assertSame('sonata.page.cms.snapshot', $services['snapshot']->__toString());
        }
    }

    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CacheCompilerPass());
    }
}
