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

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\PageBundle\Block\BreadcrumbBlockService;
use Sonata\PageBundle\Block\ChildrenPagesBlockService;
use Sonata\PageBundle\Block\ContainerBlockService;
use Sonata\PageBundle\Block\PageListBlockService;
use Sonata\PageBundle\Block\SharedBlockBlockService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.page.block.container', ContainerBlockService::class)
            ->tag('sonata.block', ['context' => 'internal'])
            ->args([
                new ReferenceConfigurator('twig'),
            ])

        ->set('sonata.page.block.children_pages', ChildrenPagesBlockService::class)
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.page.site.selector'),
                new ReferenceConfigurator('sonata.page.cms_manager_selector'),
                new ReferenceConfigurator('sonata.page.admin.page'),
            ])

        ->set('sonata.page.block.breadcrumb', BreadcrumbBlockService::class)
            ->tag('sonata.block')
            ->tag('sonata.breadcrumb')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('knp_menu.factory'),
                new ReferenceConfigurator('sonata.page.cms_manager_selector'),
            ])

        ->set('sonata.page.block.shared_block', SharedBlockBlockService::class)
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.page.manager.block'),
                new ReferenceConfigurator('sonata.page.admin.shared_block'),
            ])

        ->set('sonata.page.block.pagelist', PageListBlockService::class)
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.page.manager.page'),
            ])

        ->alias(BlockServiceManagerInterface::class, 'sonata.block.manager');
};
