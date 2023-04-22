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
use Sonata\PageBundle\Controller\BlockAdminController;
use Sonata\PageBundle\Controller\PageAdminController;
use Sonata\PageBundle\Controller\PageController;
use Sonata\PageBundle\Controller\SiteAdminController;
use Sonata\PageBundle\Controller\SnapshotAdminController;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.controller.page', PageController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->args([
                service('sonata.page.kernel.exception_listener'),
                service('sonata.page.page_service_manager'),
                service('sonata.page.cms_manager_selector'),
            ])
            ->call('setContainer', [
                service(ContainerInterface::class),
            ])

        ->set('sonata.page.controller.admin.block', BlockAdminController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [
                service(ContainerInterface::class),
            ])

        ->set('sonata.page.controller.admin.page', PageAdminController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [
                service(ContainerInterface::class),
            ])

        ->set('sonata.page.controller.admin.site', SiteAdminController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [
                service(ContainerInterface::class),
            ])

        ->set('sonata.page.controller.admin.snapshot', SnapshotAdminController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [
                service(ContainerInterface::class),
            ]);
};
