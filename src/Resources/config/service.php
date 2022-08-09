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

use Sonata\PageBundle\Service\CleanupSnapshotService;
use Sonata\PageBundle\Service\CreateSnapshotService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.page.service.create_snapshot', CreateSnapshotService::class)
            ->public()
            ->args([
                new ReferenceConfigurator('sonata.page.manager.snapshot'),
                new ReferenceConfigurator('sonata.page.manager.page'),
                new ReferenceConfigurator('sonata.page.transformer'),
            ])

        ->set('sonata.page.service.cleanup_snapshot', CleanupSnapshotService::class)
            ->public()
            ->args([
                new ReferenceConfigurator('sonata.page.manager.snapshot'),
                new ReferenceConfigurator('sonata.page.manager.page'),
            ])

        ->alias(CreateSnapshotService::class, 'sonata.page.service.create_snapshot');
};
