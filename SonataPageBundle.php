<?php

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
use Sonata\PageBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\PageBundle\DependencyInjection\Compiler\PageServiceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * SonataPageBundle.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataPageBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->registerFormMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CacheCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new PageServiceCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->registerFormMapping();

        $container = $this->container;
        $class = $this->container->getParameter('sonata.page.page.class');

        if (!class_exists($class)) {
            // we only set the method if the class exist
            return;
        }

        call_user_func(array($class, 'setSlugifyMethod'), function ($text) use ($container) {
            $id = $container->getParameter('sonata.page.slugify_service');

            $service = $container->get($id);

            return $service->slugify($text);
        });
    }

    /**
     * Register form mapping information.
     */
    public function registerFormMapping()
    {
        FormHelper::registerFormTypeMapping(array(
            'sonata_page_api_form_site' => 'Sonata\PageBundle\Form\Type\ApiSiteType',
            'sonata_page_api_form_page' => 'Sonata\PageBundle\Form\Type\ApiPageType',
            'sonata_page_api_form_block' => 'Sonata\PageBundle\Form\Type\ApiBlockType',
            'sonata_page_selector' => 'Sonata\PageBundle\Form\Type\PageSelectorType',
            'sonata_page_create_snapshot' => 'Sonata\PageBundle\Form\Type\CreateSnapshotType',
            'sonata_page_template' => 'Sonata\PageBundle\Form\Type\TemplateChoiceType',
            'sonata_page_type_choice' => 'Sonata\PageBundle\Form\Type\PageTypeChoiceType',
            'cmf_routing_route_type' => 'Symfony\Cmf\Bundle\RoutingBundle\Form\Type\RouteTypeType',
        ));
    }
}
