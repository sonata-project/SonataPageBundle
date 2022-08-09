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

use Sonata\PageBundle\CmsManager\CmsManagerSelector;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\CmsManager\CmsSnapshotManager;
use Sonata\PageBundle\CmsManager\DecoratorStrategy;
use Sonata\PageBundle\Listener\RequestListener;
use Sonata\PageBundle\Listener\ResponseListener;
use Sonata\PageBundle\Page\PageServiceManager;
use Sonata\PageBundle\Page\Service\DefaultPageService;
use Sonata\PageBundle\Page\TemplateManager;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Sonata\PageBundle\Route\CmsPageRouter;
use Sonata\PageBundle\Route\RoutePageGenerator;
use Sonata\PageBundle\Site\HostByLocaleSiteSelector;
use Sonata\PageBundle\Site\HostPathByLocaleSiteSelector;
use Sonata\PageBundle\Site\HostPathSiteSelector;
use Sonata\PageBundle\Site\HostSiteSelector;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Http\Event\LogoutEvent;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.page.site.selector.host', HostSiteSelector::class)
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onKernelRequest',
                'priority' => 47,
            ])
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onKernelRequestRedirect',
                'priority' => 0,
            ])
            ->args([
                new ReferenceConfigurator('sonata.page.manager.site'),
                new ReferenceConfigurator('sonata.page.decorator_strategy'),
                new ReferenceConfigurator('sonata.seo.page'),
            ])

        ->set('sonata.page.site.selector.host_by_locale', HostByLocaleSiteSelector::class)
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onKernelRequest',
                'priority' => 47,
            ])
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onKernelRequestRedirect',
                'priority' => 0,
            ])
            ->args([
                new ReferenceConfigurator('sonata.page.manager.site'),
                new ReferenceConfigurator('sonata.page.decorator_strategy'),
                new ReferenceConfigurator('sonata.seo.page'),
            ])

        ->set('sonata.page.site.selector.host_with_path', HostPathSiteSelector::class)
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onKernelRequest',
                'priority' => 47,
            ])
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onKernelRequestRedirect',
                'priority' => 0,
            ])
            ->args([
                new ReferenceConfigurator('sonata.page.manager.site'),
                new ReferenceConfigurator('sonata.page.decorator_strategy'),
                new ReferenceConfigurator('sonata.seo.page'),
            ])

        ->set('sonata.page.site.selector.host_with_path_by_locale', HostPathByLocaleSiteSelector::class)
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onKernelRequest',
                'priority' => 47,
            ])
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onKernelRequestRedirect',
                'priority' => 0,
            ])
            ->args([
                new ReferenceConfigurator('sonata.page.manager.site'),
                new ReferenceConfigurator('sonata.page.decorator_strategy'),
                new ReferenceConfigurator('sonata.seo.page'),
            ])

        ->set('sonata.page.response_listener', ResponseListener::class)
            ->tag('kernel.event_listener', [
                'event' => 'kernel.response',
                'method' => 'onCoreResponse',
                'priority' => -1,
            ])
            ->args([
                new ReferenceConfigurator('sonata.page.cms_manager_selector'),
                new ReferenceConfigurator('sonata.page.page_service_manager'),
                new ReferenceConfigurator('sonata.page.decorator_strategy'),
                new ReferenceConfigurator('twig'),
                '%sonata.page.skip_redirection%',
            ])

        ->set('sonata.page.request_listener', RequestListener::class)
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onCoreRequest',
                'priority' => 4,
            ])
            ->args([
                new ReferenceConfigurator('sonata.page.cms_manager_selector'),
                new ReferenceConfigurator('sonata.page.site.selector'),
                new ReferenceConfigurator('sonata.page.decorator_strategy'),
            ])

        ->set('sonata.page.cms_manager_selector', CmsManagerSelector::class)
            ->public()
            ->tag('kernel.event_listener', [
                'event' => 'security.interactive_login',
                'method' => 'onSecurityInteractiveLogin',
            ])
            ->tag('kernel.event_listener', [
                'event' => LogoutEvent::class,
                'method' => 'onLogout',
            ])
            ->args([
                new ReferenceConfigurator('sonata.page.cms.page'),
                new ReferenceConfigurator('sonata.page.cms.snapshot'),
                new ReferenceConfigurator('sonata.page.admin.page'),
                new ReferenceConfigurator('security.token_storage'),
                new ReferenceConfigurator('request_stack'),
            ])

        ->set('sonata.page.cms.page', CmsPageManager::class)
            ->public()
            ->tag('sonata.page.manager', ['type' => 'page'])
            ->args([
                new ReferenceConfigurator('sonata.page.manager.page'),
                new ReferenceConfigurator('sonata.page.block_interactor'),
            ])

        ->set('sonata.page.cms.snapshot', CmsSnapshotManager::class)
            ->public()
            ->tag('sonata.page.manager', ['type' => 'snapshot'])
            ->args([
                new ReferenceConfigurator('sonata.page.manager.snapshot'),
                new ReferenceConfigurator('sonata.page.transformer'),
            ])

        ->set('sonata.page.decorator_strategy', DecoratorStrategy::class)
            ->args([[], [], []])

        ->set('sonata.page.router.request_context', RequestContext::class)
            ->factory([
                new ReferenceConfigurator('sonata.page.site.selector'),
                'getRequestContext',
            ])

        ->set('sonata.page.router', CmsPageRouter::class)
            ->public()
            ->args([
                new ReferenceConfigurator('router.request_context'),
                new ReferenceConfigurator('sonata.page.cms_manager_selector'),
                new ReferenceConfigurator('sonata.page.site.selector'),
                new ReferenceConfigurator('router.default'),
            ])

        ->set('sonata.page.route.page.generator', RoutePageGenerator::class)
            ->public()
            ->args([
                new ReferenceConfigurator('router.default'),
                new ReferenceConfigurator('sonata.page.manager.page'),
                new ReferenceConfigurator('sonata.page.decorator_strategy'),
                new ReferenceConfigurator('sonata.page.kernel.exception_listener'),
            ])

        ->set('sonata.page.template_manager', TemplateManager::class)
            ->public()
            ->args([
                new ReferenceConfigurator('twig'),
                [],
            ])

        ->set('sonata.page.page_service_manager', PageServiceManager::class)
            ->public()

        ->set('sonata.page.service.default', DefaultPageService::class)
            ->public()
            ->tag('sonata.page')
            ->args([
                'Default',
                new ReferenceConfigurator('sonata.page.template_manager'),
                new ReferenceConfigurator('sonata.seo.page'),
            ])

        ->alias(TemplateManagerInterface::class, 'sonata.page.template_manager')

        ->alias(SiteSelectorInterface::class, 'sonata.page.site.selector');
};
