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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

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
use Sonata\PageBundle\Serializer\BlockTypeExtractor;
use Sonata\PageBundle\Serializer\InterfaceTypeExtractor;
use Symfony\Component\String\Slugger\SluggerInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.proxy.snapshot.factory', SnapshotPageProxyFactory::class)
            ->public()
            ->args([
                SnapshotPageProxy::class,
            ])

        ->set('sonata.page.manager.page', PageManager::class)
            ->public()
            ->args([
                param('sonata.page.page.class'),
                service('doctrine'),
                service(SluggerInterface::class),
                abstract_arg('defaults'),
                abstract_arg('page defaults'),
            ])

        ->set('sonata.page.manager.snapshot', SnapshotManager::class)
            ->public()
            ->args([
                param('sonata.page.snapshot.class'),
                service('doctrine'),
                service('sonata.page.proxy.snapshot.factory'),
            ])

        ->set('sonata.page.manager.block', BlockManager::class)
            ->public()
            ->args([
                param('sonata.page.block.class'),
                service('doctrine'),
            ])

        ->set('sonata.page.manager.site', SiteManager::class)
            ->public()
            ->args([
                param('sonata.page.site.class'),
                service('doctrine'),
            ])

        ->set('sonata.page.block_interactor', BlockInteractor::class)
            ->public()
            ->args([
                service('doctrine'),
                service('sonata.page.manager.block'),
            ])

        ->set('sonata.page.transformer', Transformer::class)
            ->public()
            ->args([
                service('sonata.page.manager.snapshot'),
                service('sonata.page.manager.page'),
                service('sonata.page.manager.block'),
                service('doctrine'),
                service('serializer'),
            ])

        ->set('sonata.page.block_type_extractor', BlockTypeExtractor::class)
            ->public()
            ->tag('property_info.type_extractor')
            ->args([
                param('sonata.page.block.class'),
            ])

        ->set('sonata.page.interface_type_extractor', InterfaceTypeExtractor::class)
            ->public()
            ->tag('property_info.type_extractor')
            ->args([
                param('sonata.page.page.class'),
                param('sonata.page.block.class'),
            ])

        ->alias(PageManagerInterface::class, 'sonata.page.manager.page')

        ->alias(SnapshotManagerInterface::class, 'sonata.page.manager.snapshot')

        ->alias(SiteManagerInterface::class, 'sonata.page.manager.site')

        ->alias(BlockInteractorInterface::class, 'sonata.page.block_interactor');
};
