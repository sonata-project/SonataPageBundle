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

use Sonata\PageBundle\Listener\ExceptionListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.page.kernel.exception_listener', ExceptionListener::class)
            ->public()
            ->tag('kernel.event_listener', [
                'event' => 'kernel.exception',
                'method' => 'onKernelException',
                'priority' => -127,
            ])
            ->tag('monolog.logger', ['channel' => 'request'])
            ->args([
                new ReferenceConfigurator('sonata.page.site.selector'),
                new ReferenceConfigurator('sonata.page.cms_manager_selector'),
                '%kernel.debug%',
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.page.page_service_manager'),
                new ReferenceConfigurator('sonata.page.decorator_strategy'),
                [],
                (new ReferenceConfigurator('logger'))->nullOnInvalid(),
            ]);
};
