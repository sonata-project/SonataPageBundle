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
use Sonata\PageBundle\Admin\Extension\CreateSnapshotAdminExtension;
use Sonata\PageBundle\Admin\PageAdmin;
use Sonata\PageBundle\Admin\SharedBlockAdmin;
use Sonata\PageBundle\Admin\SiteAdmin;
use Sonata\PageBundle\Admin\SnapshotAdmin;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.page.admin.page', PageAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => '%sonata.page.admin.page.entity%',
                'controller' => 'sonata.page.controller.admin.page',
                'manager_type' => 'orm',
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'page',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->args([
                new ReferenceConfigurator('sonata.page.manager.page'),
                new ReferenceConfigurator('sonata.page.manager.site'),
            ])
            ->call('addChild', [
                new ReferenceConfigurator('sonata.page.admin.snapshot'),
                'page',
            ])
            ->call('addChild', [
                new ReferenceConfigurator('sonata.page.admin.block'),
                'page',
            ])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.block', BlockAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => '%sonata.page.admin.block.entity%',
                'controller' => 'sonata.page.controller.admin.block',
                'manager_type' => 'orm',
                'show_in_dashboard' => false,
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'block',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->args([
                new ReferenceConfigurator('sonata.block.manager'),
                '%sonata_block.blocks%',
            ])
            ->call('setContainerBlockTypes', ['%sonata.block.container.types%'])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.shared_block', SharedBlockAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => '%sonata.page.admin.block.entity%',
                'controller' => 'sonata.page.controller.admin.block',
                'manager_type' => 'orm',
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'shared_block',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->args([
                new ReferenceConfigurator('sonata.block.manager'),
            ])
            ->call('setContainerBlockTypes', ['%sonata.block.container.types%'])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.snapshot', SnapshotAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => '%sonata.page.admin.snapshot.entity%',
                'controller' => 'sonata.page.controller.admin.snapshot',
                'manager_type' => 'orm',
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'snapshot',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.site', SiteAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => '%sonata.page.admin.site.entity%',
                'controller' => 'sonata.page.controller.admin.site',
                'manager_type' => 'orm',
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'site',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->args([
                new ReferenceConfigurator('sonata.page.route.page.generator'),
            ])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.extension.snapshot', CreateSnapshotAdminExtension::class)
            ->tag('sonata.admin.extension', ['target' => 'sonata.page.admin.page'])
            ->tag('sonata.admin.extension', ['target' => 'sonata.page.admin.block'])
            ->args([
                new ReferenceConfigurator('sonata.page.service.create_snapshot'),
            ])

        ->alias(PageAdmin::class, 'sonata.page.admin.page')

        ->alias(BlockAdmin::class, 'sonata.page.admin.block')

        ->alias(SnapshotAdmin::class, 'sonata.page.admin.snapshot');
};
