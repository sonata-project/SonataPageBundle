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

use Sonata\PageBundle\Form\Type\CreateSnapshotType;
use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Form\Type\PageTypeChoiceType;
use Sonata\PageBundle\Form\Type\TemplateChoiceType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.page.form.type.page_selector', PageSelectorType::class)
            ->tag('form.type', ['alias' => 'sonata_page_selector'])
            ->args([
                new ReferenceConfigurator('sonata.page.manager.page'),
            ])

        ->set('sonata.page.form.create_snapshot', CreateSnapshotType::class)
            ->tag('form.type', ['alias' => 'sonata_page_create_snapshot'])

        ->set('sonata.page.form.template_choice', TemplateChoiceType::class)
            ->tag('form.type', ['alias' => 'sonata_page_template'])
            ->args([
                new ReferenceConfigurator('sonata.page.template_manager'),
            ])

        ->set('sonata.page.form.page_type_choice', PageTypeChoiceType::class)
            ->tag('form.type', ['alias' => 'sonata_page_type_choice'])
            ->args([
                new ReferenceConfigurator('sonata.page.page_service_manager'),
            ]);
};
