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

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\XmlFileLoader;

return static function (RoutingConfigurator $routes) {
    foreach (debug_backtrace() as $trace) {
        if (isset($trace['object'], $trace['args'])
            && class_exists(XmlFileLoader::class)
            && $trace['object'] instanceof XmlFileLoader
            && $trace['args'][0] === __DIR__.'/exceptions.php'
            && $trace['args'][3] === __DIR__.'/exceptions.xml'
        ) {
            @trigger_error(
                sprintf(
                    'The "%s/exceptions.xml" routing configuration is deprecated since sonata-project/page-bundle 4.10. Import "exceptions.php" instead.',
                    __DIR__,
                ),
                \E_USER_DEPRECATED
            );

            break;
        }
    }

    $routes->add('sonata_page_exceptions_list', '/exceptions/list')
        ->controller('sonata.page.controller.page::exceptionsList');

    $routes->add('sonata_page_exceptions_edit', '/exceptions/edit/{code}')
        ->controller('sonata.page.controller.page::exceptionEdit')
        ->requirements(['code' => '\d+']);
};
