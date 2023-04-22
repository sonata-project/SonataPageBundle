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

use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Form\Type\PageTypeChoiceType;
use Sonata\PageBundle\Form\Type\TemplateChoiceType;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.page.form.type.page_selector', PageSelectorType::class)
            ->tag('form.type', ['alias' => 'sonata_page_selector'])
            ->args([
                service('sonata.page.manager.page'),
            ])

        ->set('sonata.page.form.template_choice', TemplateChoiceType::class)
            ->tag('form.type', ['alias' => 'sonata_page_template'])
            ->args([
                service('sonata.page.template_manager'),
            ])

        ->set('sonata.page.form.page_type_choice', PageTypeChoiceType::class)
            ->tag('form.type', ['alias' => 'sonata_page_type_choice'])
            ->args([
                service('sonata.page.page_service_manager'),
            ]);
};
