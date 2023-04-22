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

use Sonata\PageBundle\Twig\Extension\PageExtension;
use Sonata\PageBundle\Twig\GlobalVariables;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.twig.extension', PageExtension::class)
            ->tag('twig.extension')
            ->args([
                service('sonata.page.cms_manager_selector'),
                service('sonata.page.site.selector'),
                service('router'),
                service('sonata.block.templating.helper'),
                service('request_stack'),
                param('sonata.page.hide_disabled_blocks'),
            ])

        ->set('sonata.page.twig.global', GlobalVariables::class)
            ->args([
                service('sonata.page.manager.site'),
                service('sonata.page.cms_manager_selector'),
                service('sonata.page.site.selector'),
                service('sonata.page.template_manager'),
            ]);
};
