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

use Sonata\PageBundle\Entity\BlockInteractor;
use Sonata\PageBundle\Entity\BlockManager;
use Sonata\PageBundle\Entity\PageManager;
use Sonata\PageBundle\Entity\SiteManager;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Entity\Transformer;
use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Model\SnapshotPageProxyFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.page.proxy.snapshot.factory', SnapshotPageProxyFactory::class)
            ->public()
            ->args([
                SnapshotPageProxy::class,
            ])

        ->set('sonata.page.manager.page', PageManager::class)
            ->public()
            ->args([
                '%sonata.page.page.class%',
                new ReferenceConfigurator('doctrine'),
                new ReferenceConfigurator('sonata.page.slugify.cocur'),
                [],
                [],
            ])

        ->set('sonata.page.manager.snapshot', SnapshotManager::class)
            ->public()
            ->args([
                '%sonata.page.snapshot.class%',
                new ReferenceConfigurator('doctrine'),
                new ReferenceConfigurator('sonata.page.proxy.snapshot.factory'),
            ])

        ->set('sonata.page.manager.block', BlockManager::class)
            ->public()
            ->args([
                '%sonata.page.block.class%',
                new ReferenceConfigurator('doctrine'),
            ])

        ->set('sonata.page.manager.site', SiteManager::class)
            ->public()
            ->args([
                '%sonata.page.site.class%',
                new ReferenceConfigurator('doctrine'),
            ])

        ->set('sonata.page.block_interactor', BlockInteractor::class)
            ->public()
            ->args([
                new ReferenceConfigurator('doctrine'),
                new ReferenceConfigurator('sonata.page.manager.block'),
            ])

        ->set('sonata.page.transformer', Transformer::class)
            ->public()
            ->args([
                new ReferenceConfigurator('sonata.page.manager.snapshot'),
                new ReferenceConfigurator('sonata.page.manager.page'),
                new ReferenceConfigurator('sonata.page.manager.block'),
                new ReferenceConfigurator('doctrine'),
            ])

        ->alias(PageManagerInterface::class, 'sonata.page.manager.page')

        ->alias(SnapshotManagerInterface::class, 'sonata.page.manager.snapshot')

        ->alias(SiteManagerInterface::class, 'sonata.page.manager.site')

        ->alias(BlockInteractorInterface::class, 'sonata.page.block_interactor');
};
