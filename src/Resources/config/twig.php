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

use Sonata\PageBundle\Twig\Extension\PageExtension;
use Sonata\PageBundle\Twig\GlobalVariables;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.page.twig.extension', PageExtension::class)
            ->tag('twig.extension')
            ->args([
                new ReferenceConfigurator('sonata.page.cms_manager_selector'),
                new ReferenceConfigurator('sonata.page.site.selector'),
                new ReferenceConfigurator('router'),
                new ReferenceConfigurator('sonata.block.templating.helper'),
                new ReferenceConfigurator('request_stack'),
                '%sonata.page.hide_disabled_blocks%',
            ])

        ->set('sonata.page.twig.global', GlobalVariables::class)
            ->args([
                new ReferenceConfigurator('sonata.page.manager.site'),
                new ReferenceConfigurator('sonata.page.cms_manager_selector'),
                new ReferenceConfigurator('sonata.page.site.selector'),
                new ReferenceConfigurator('sonata.page.template_manager'),
            ]);
};
