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

namespace Sonata\PageBundle;

use Sonata\CoreBundle\Form\FormHelper;
use Sonata\PageBundle\DependencyInjection\Compiler\CacheCompilerPass;
use Sonata\PageBundle\DependencyInjection\Compiler\CmfRouterCompilerPass;
use Sonata\PageBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\PageBundle\DependencyInjection\Compiler\PageServiceCompilerPass;
use Sonata\PageBundle\Form\Type\ApiBlockType;
use Sonata\PageBundle\Form\Type\ApiPageType;
use Sonata\PageBundle\Form\Type\ApiSiteType;
use Sonata\PageBundle\Form\Type\CreateSnapshotType;
use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Form\Type\PageTypeChoiceType;
use Sonata\PageBundle\Form\Type\TemplateChoiceType;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Type\RouteTypeType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * SonataPageBundle.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataPageBundle extends Bundle
{
    public function init(): void
    {
        $this->registerFormMapping();
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new CacheCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new PageServiceCompilerPass());
        $container->addCompilerPass(new CmfRouterCompilerPass());
    }

    public function boot(): void
    {
        $this->registerFormMapping();

        $container = $this->container;
        $class = $this->container->getParameter('sonata.page.page.class');

        if (!class_exists($class)) {
            // we only set the method if the class exist
            return;
        }

        \call_user_func([$class, 'setSlugifyMethod'], static function ($text) use ($container) {
            // NEXT_MAJOR: remove this check
            if ($container->hasParameter('sonata.page.slugify_service')) {
                $id = $container->getParameter('sonata.page.slugify_service');
            } else {
                @trigger_error(
                    'The "sonata.core.slugify.native" service is deprecated since 2.3.9, to be removed in 4.0. '.
                    'Use "sonata.core.slugify.cocur" service through config instead.',
                    E_USER_DEPRECATED
                );

                // default BC value, you should use sonata.core.slugify.cocur
                $id = 'sonata.core.slugify.native';
            }

            $service = $container->get($id);

            return $service->slugify($text);
        });
    }

    /**
     * Register form mapping information.
     *
     * NEXT_MAJOR: remove this method
     */
    public function registerFormMapping(): void
    {
        if (class_exists(FormHelper::class)) {
            FormHelper::registerFormTypeMapping([
                'sonata_page_api_form_site' => ApiSiteType::class,
                'sonata_page_api_form_page' => ApiPageType::class,
                'sonata_page_api_form_block' => ApiBlockType::class,
                'sonata_page_selector' => PageSelectorType::class,
                'sonata_page_create_snapshot' => CreateSnapshotType::class,
                'sonata_page_template' => TemplateChoiceType::class,
                'sonata_page_type_choice' => PageTypeChoiceType::class,
                'cmf_routing_route_type' => RouteTypeType::class,
            ]);
        }
    }
}
