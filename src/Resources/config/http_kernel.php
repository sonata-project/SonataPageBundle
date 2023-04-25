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

use Sonata\PageBundle\Listener\ExceptionListener;

return static function (ContainerConfigurator $containerConfigurator): void {
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
                service('sonata.page.site.selector'),
                service('sonata.page.cms_manager_selector'),
                param('kernel.debug'),
                service('twig'),
                service('sonata.page.page_service_manager'),
                service('sonata.page.decorator_strategy'),
                abstract_arg('http error codes'),
                service('logger')->nullOnInvalid(),
            ]);
};
