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

use Sonata\PageBundle\Command\CleanupSnapshotsCommand;
use Sonata\PageBundle\Command\CloneSiteCommand;
use Sonata\PageBundle\Command\CreateBlockContainerCommand;
use Sonata\PageBundle\Command\CreateSiteCommand;
use Sonata\PageBundle\Command\CreateSnapshotsCommand;
use Sonata\PageBundle\Command\UpdateCoreRoutesCommand;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.command.cleanup_snapshots', CleanupSnapshotsCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.page.service.cleanup_snapshot'),
                service('sonata.page.manager.site'),
            ])

        ->set('sonata.page.command.clone_site', CloneSiteCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.page.manager.site'),
                service('sonata.page.manager.page'),
                service('sonata.page.manager.block'),
            ])

        ->set('sonata.page.command.create_block_container', CreateBlockContainerCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.page.manager.page'),
                service('sonata.page.block_interactor'),
            ])

        ->set('sonata.page.command.create_site', CreateSiteCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.page.manager.site'),
            ])

        ->set('sonata.page.command.create_snapshots', CreateSnapshotsCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.page.service.create_snapshot'),
                service('sonata.page.manager.site'),
            ])

        ->set('sonata.page.command.update_core_routes', UpdateCoreRoutesCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.page.manager.site'),
                service('sonata.page.route.page.generator'),
            ]);
};
