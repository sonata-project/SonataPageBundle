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

use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use Sonata\PageBundle\Tests\App\Controller\DynamicController;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set(DynamicController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [service(ContainerInterface::class)])

        ->set('logger', NullLogger::class);
};
