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

use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetterInterface;

return static function (ContainerConfigurator $container): void {
    $container->import('doctrine.yaml');
    $container->import('sonata.yaml');

    $container->extension('framework', [
        'test' => true,
        'secret' => '50n474.U53r',
        'translator' => [
            'enabled' => true,
        ],
        'form' => [
            'enabled' => true,
        ],
        'csrf_protection' => [
            'enabled' => false,
        ],
        'http_method_override' => false,
        'session' => [
            'storage_factory_id' => 'session.storage.factory.mock_file',
        ],
    ]);

    /*
     * TODO: When drop suport for symfony 7.2.* and 6.4.*, keep only one framework config.
     */
    if (class_exists(ServicesResetterInterface::class)) {
        $container->extension('framework', [
            'property_info' => [
                'with_constructor_extractor' => true,
            ],
        ]);
    }

    $container->extension('security', [
        'role_hierarchy' => null,
        'firewalls' => [
            'test' => [
                'security' => false,
            ],
        ],
        'access_control' => null,
    ]);

    $container->extension('twig', [
        'strict_variables' => true,
    ]);
};
