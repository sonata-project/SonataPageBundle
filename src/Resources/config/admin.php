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

use Sonata\PageBundle\Admin\BlockAdmin;
use Sonata\PageBundle\Admin\PageAdmin;
use Sonata\PageBundle\Admin\SharedBlockAdmin;
use Sonata\PageBundle\Admin\SiteAdmin;
use Sonata\PageBundle\Admin\SnapshotAdmin;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {

    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->parameters()

        ->set('sonata.page.admin.groupname', 'sonata_page')

        ->set('sonata.page.admin.groupicon', "<i class='fa fa-sitemap'></i>")

        ->set('sonata.page.admin.page.translation_domain', 'SonataPageBundle')
        ->set('sonata.page.admin.site.translation_domain', '%sonata.page.admin.page.translation_domain%')
        ->set('sonata.page.admin.block.translation_domain', '%sonata.page.admin.page.translation_domain%')
        ->set('sonata.page.admin.shared_block.translation_domain', '%sonata.page.admin.page.translation_domain%')
        ->set('sonata.page.admin.snapshot.translation_domain', '%sonata.page.admin.page.translation_domain%');

    $containerConfigurator->services()

        ->set('sonata.page.admin.page', PageAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'manager_type' => 'orm',
                'group' => '%sonata.page.admin.groupname%',
                'label_catalogue' => '%sonata.page.admin.page.translation_domain%',
                'label' => 'page',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '%sonata.page.admin.groupicon%',
            ])
            ->args([
                '',
                '%sonata.page.page.class%',
                'sonata.page.controller.page.admin',
            ])
            ->call('addChild', [new ReferenceConfigurator('sonata.page.admin.block'), 'page'])
            ->call('addChild', [new ReferenceConfigurator('sonata.page.admin.snapshot'), 'page'])
            ->call('setPageManager', [new ReferenceConfigurator('sonata.page.manager.page')])
            ->call('setCacheManager', [new ReferenceConfigurator('sonata.cache.manager')])
            ->call('setSiteManager', [new ReferenceConfigurator('sonata.page.manager.site')])
            ->call('setTranslationDomain', ['%sonata.page.admin.page.translation_domain%'])

        ->alias(PageAdmin::class, 'sonata.page.admin.page')

        ->set('sonata.page.admin.block', BlockAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'show_in_dashboard' => false,
                'manager_type' => 'orm',
                'group' => '%sonata.page.admin.groupname%',
                'label_catalogue' => '%sonata.page.admin.block.translation_domain%',
                'label' => 'block',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '%sonata.page.admin.groupicon%',
                'default' => true,
            ])
            ->args([
                '',
                '%sonata.page.block.class%',
                'sonata.page.controller.block.admin',
                '%sonata_block.blocks%',
            ])
            ->call('setCacheManager', [new ReferenceConfigurator('sonata.cache.manager')])
            ->call('setBlockManager', [new ReferenceConfigurator('sonata.block.manager')])
            ->call('setTranslationDomain', ['%sonata.page.admin.block.translation_domain%'])
            ->call('setContainerBlockTypes', ['%sonata.block.container.types%'])

        ->alias(BlockAdmin::class, 'sonata.page.admin.block')

        ->set('sonata.page.admin.shared_block', SharedBlockAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'manager_type' => 'orm',
                'group' => '%sonata.page.admin.groupname%',
                'label_catalogue' => '%sonata.page.admin.shared_block.translation_domain%',
                'label' => 'shared_block',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '%sonata.page.admin.groupicon%',
            ])
            ->args([
                '',
                '%sonata.page.block.class%',
                'sonata.page.controller.block.admin',
                '%sonata_block.blocks%',
            ])
            ->call('setCacheManager', [new ReferenceConfigurator('sonata.cache.manager')])
            ->call('setBlockManager', [new ReferenceConfigurator('sonata.block.manager')])
            ->call('setTranslationDomain', ['%sonata.page.admin.shared_block.translation_domain%'])
            ->call('setContainerBlockTypes', ['%sonata.block.container.types%'])

        ->set('sonata.page.admin.snapshot', SnapshotAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'manager_type' => 'orm',
                'group' => '%sonata.page.admin.groupname%',
                'label_catalogue' => '%sonata.page.admin.snapshot.translation_domain%',
                'label' => 'snapshot',
                'show_in_dashboard' => false,
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '%sonata.page.admin.groupicon%',
            ])
            ->args([
                '',
                '%sonata.page.snapshot.class%',
                'sonata.page.controller.snapshot.admin',
            ])
            ->call('setCacheManager', [new ReferenceConfigurator('sonata.cache.manager')])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.site', SiteAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'manager_type' => 'orm',
                'group' => '%sonata.page.admin.groupname%',
                'label_catalogue' => '%sonata.page.admin.site.translation_domain%',
                'label' => 'site',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '%sonata.page.admin.groupicon%',
            ])
            ->args([
                '',
                '%sonata.page.site.class%',
                'sonata.page.controller.site.admin',
                new ReferenceConfigurator('sonata.page.route.page.generator'),
            ])
            ->call('setTranslationDomain', ['%sonata.page.admin.site.translation_domain%']);

// TODO : fix sonata.notification.backend
//
//        ->set('sonata.page.admin.extension.snapshot', CreateSnapshotAdminExtension::class)
//            ->tag('sonata.admin.extension', [
//                'target' => 'sonata.page.admin.page',
//            ])
//            ->tag('sonata.admin.extension', [
//                'target' => 'sonata.page.admin.block',
//            ])
//            ->args([
//                new ReferenceConfigurator('sonata.notification.backend'),
//            ])
//            ->call('setTranslationDomain', ['%sonata.page.admin.site.translation_domain%'])
//        ;
};
