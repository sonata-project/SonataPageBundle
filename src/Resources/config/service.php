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

use Sonata\PageBundle\Service\CleanupSnapshotService;
use Sonata\PageBundle\Service\CreateSnapshotService;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.service.create_snapshot', CreateSnapshotService::class)
            ->public()
            ->args([
                service('sonata.page.manager.snapshot'),
                service('sonata.page.manager.page'),
                service('sonata.page.transformer'),
            ])

        ->set('sonata.page.service.cleanup_snapshot', CleanupSnapshotService::class)
            ->public()
            ->args([
                service('sonata.page.manager.snapshot'),
                service('sonata.page.manager.page'),
            ])

        ->alias(CreateSnapshotService::class, 'sonata.page.service.create_snapshot');
};
