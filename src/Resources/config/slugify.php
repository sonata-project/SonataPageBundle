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

use Cocur\Slugify\Slugify;

// NEXT_MAJOR: Remove this file.
return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.slugify.cocur', Slugify::class)
            ->deprecate(
                'sonata-project/page-bundle',
                '4.11.0',
                'Service "%service_id%" is deprecated.',
            )
            ->public();
};
