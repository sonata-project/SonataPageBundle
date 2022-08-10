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

use Psr\Container\ContainerInterface;
use Sonata\PageBundle\Controller\BlockAdminController;
use Sonata\PageBundle\Controller\BlockController;
use Sonata\PageBundle\Controller\PageAdminController;
use Sonata\PageBundle\Controller\PageController;
use Sonata\PageBundle\Controller\SiteAdminController;
use Sonata\PageBundle\Controller\SnapshotAdminController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.page.controller.block', BlockController::class)
            ->public()

        ->set('sonata.page.controller.page', PageController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->args([
                new ReferenceConfigurator('sonata.page.kernel.exception_listener'),
                new ReferenceConfigurator('sonata.page.page_service_manager'),
                new ReferenceConfigurator('sonata.page.cms_manager_selector'),
            ])
            ->call('setContainer', [
                new ReferenceConfigurator(ContainerInterface::class),
            ])

        ->set('sonata.page.controller.admin.block', BlockAdminController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [
                new ReferenceConfigurator(ContainerInterface::class),
            ])

        ->set('sonata.page.controller.admin.page', PageAdminController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [
                new ReferenceConfigurator(ContainerInterface::class),
            ])

        ->set('sonata.page.controller.admin.site', SiteAdminController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [
                new ReferenceConfigurator(ContainerInterface::class),
            ])

        ->set('sonata.page.controller.admin.snapshot', SnapshotAdminController::class)
            ->public()
            ->tag('container.service_subscriber')
            ->call('setContainer', [
                new ReferenceConfigurator(ContainerInterface::class),
            ]);
};
