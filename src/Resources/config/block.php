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

use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\PageBundle\Block\BreadcrumbBlockService;
use Sonata\PageBundle\Block\ChildrenPagesBlockService;
use Sonata\PageBundle\Block\ContainerBlockService;
use Sonata\PageBundle\Block\PageListBlockService;
use Sonata\PageBundle\Block\SharedBlockBlockService;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.block.container', ContainerBlockService::class)
            ->tag('sonata.block', ['context' => 'internal'])
            ->args([
                service('twig'),
            ])

        ->set('sonata.page.block.children_pages', ChildrenPagesBlockService::class)
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.page.site.selector'),
                service('sonata.page.cms_manager_selector'),
                service('sonata.page.admin.page'),
            ])

        ->set('sonata.page.block.breadcrumb', BreadcrumbBlockService::class)
            ->tag('sonata.block')
            ->tag('sonata.breadcrumb')
            ->args([
                service('twig'),
                service('knp_menu.factory'),
                service('sonata.page.cms_manager_selector'),
            ])

        ->set('sonata.page.block.shared_block', SharedBlockBlockService::class)
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.page.manager.block'),
                service('sonata.page.admin.shared_block'),
            ])

        ->set('sonata.page.block.pagelist', PageListBlockService::class)
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.page.manager.page'),
            ])

        ->alias(BlockServiceManagerInterface::class, 'sonata.block.manager');
};
