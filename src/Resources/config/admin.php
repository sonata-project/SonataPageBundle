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

use Sonata\PageBundle\Admin\BlockAdmin;
use Sonata\PageBundle\Admin\Extension\CreateSnapshotAdminExtension;
use Sonata\PageBundle\Admin\PageAdmin;
use Sonata\PageBundle\Admin\SharedBlockAdmin;
use Sonata\PageBundle\Admin\SiteAdmin;
use Sonata\PageBundle\Admin\SnapshotAdmin;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.admin.page', PageAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => (string) param('sonata.page.admin.page.entity'),
                'controller' => 'sonata.page.controller.admin.page',
                'manager_type' => 'orm',
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'page',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->args([
                service('sonata.page.manager.page'),
                service('sonata.page.manager.site'),
            ])
            ->call('addChild', [
                service('sonata.page.admin.snapshot'),
                'page',
            ])
            ->call('addChild', [
                service('sonata.page.admin.block'),
                'page',
            ])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.block', BlockAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => (string) param('sonata.page.admin.block.entity'),
                'controller' => 'sonata.page.controller.admin.block',
                'manager_type' => 'orm',
                'show_in_dashboard' => false,
                'default' => true,
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'block',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->args([
                service('sonata.block.manager'),
                param('sonata_block.blocks'),
            ])
            ->call('setContainerBlockTypes', [param('sonata.block.container.types')])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.shared_block', SharedBlockAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => (string) param('sonata.page.admin.block.entity'),
                'controller' => 'sonata.page.controller.admin.block',
                'manager_type' => 'orm',
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'shared_block',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->args([
                service('sonata.block.manager'),
            ])
            ->call('setContainerBlockTypes', [param('sonata.block.container.types')])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.snapshot', SnapshotAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => (string) param('sonata.page.admin.snapshot.entity'),
                'controller' => 'sonata.page.controller.admin.snapshot',
                'manager_type' => 'orm',
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'snapshot',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->args([
                service('sonata.page.transformer'),
                service('sonata.page.manager.page'),
                service('sonata.page.manager.snapshot'),
            ])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.site', SiteAdmin::class)
            ->public()
            ->tag('sonata.admin', [
                'model_class' => (string) param('sonata.page.admin.site.entity'),
                'controller' => 'sonata.page.controller.admin.site',
                'manager_type' => 'orm',
                'group' => 'sonata_page',
                'translation_domain' => 'SonataPageBundle',
                'label' => 'site',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-sitemap\'></i>',
            ])
            ->args([
                service('sonata.page.route.page.generator'),
            ])
            ->call('setTranslationDomain', ['SonataPageBundle'])

        ->set('sonata.page.admin.extension.snapshot', CreateSnapshotAdminExtension::class)
            ->tag('sonata.admin.extension', ['target' => 'sonata.page.admin.page'])
            ->tag('sonata.admin.extension', ['target' => 'sonata.page.admin.block'])
            ->args([
                service('sonata.page.service.create_snapshot'),
            ])

        ->alias(BlockAdmin::class, 'sonata.page.admin.block')

        ->alias(SnapshotAdmin::class, 'sonata.page.admin.snapshot');
};
