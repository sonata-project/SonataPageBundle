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
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

return static function (ContainerConfigurator $containerConfigurator): void {
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
                service('sonata.page.manager.site'),
                service('sonata.page.decorator_strategy'),
                service('sonata.seo.page'),
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
                service('sonata.page.manager.site'),
                service('sonata.page.decorator_strategy'),
                service('sonata.seo.page'),
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
                service('sonata.page.manager.site'),
                service('sonata.page.decorator_strategy'),
                service('sonata.seo.page'),
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
                service('sonata.page.manager.site'),
                service('sonata.page.decorator_strategy'),
                service('sonata.seo.page'),
            ])

        ->set('sonata.page.response_listener', ResponseListener::class)
            ->tag('kernel.event_listener', [
                'event' => 'kernel.response',
                'method' => 'onCoreResponse',
                'priority' => -1,
            ])
            ->args([
                service('sonata.page.cms_manager_selector'),
                service('sonata.page.page_service_manager'),
                service('sonata.page.decorator_strategy'),
                service('twig'),
                param('sonata.page.skip_redirection'),
            ])

        ->set('sonata.page.request_listener', RequestListener::class)
            ->tag('kernel.event_listener', [
                'event' => 'kernel.request',
                'method' => 'onCoreRequest',
                'priority' => 4,
            ])
            ->args([
                service('sonata.page.cms_manager_selector'),
                service('sonata.page.site.selector'),
                service('sonata.page.decorator_strategy'),
            ])

        ->set('sonata.page.cms_manager_selector', CmsManagerSelector::class)
            ->public()
            ->tag('kernel.event_listener', [
                'event' => LoginSuccessEvent::class,
                'method' => 'onLoginSuccess',
            ])
            ->tag('kernel.event_listener', [
                'event' => 'security.interactive_login',
                'method' => 'onSecurityInteractiveLogin',
            ])
            ->tag('kernel.event_listener', [
                'event' => LogoutEvent::class,
                'method' => 'onLogout',
            ])
            ->args([
                service('sonata.page.cms.page'),
                service('sonata.page.cms.snapshot'),
                service('sonata.page.admin.page'),
                service('security.token_storage'),
                service('request_stack'),
            ])

        ->set('sonata.page.cms.page', CmsPageManager::class)
            ->public()
            ->tag('sonata.page.manager', ['type' => 'page'])
            ->args([
                service('sonata.page.manager.page'),
                service('sonata.page.block_interactor'),
            ])

        ->set('sonata.page.cms.snapshot', CmsSnapshotManager::class)
            ->public()
            ->tag('sonata.page.manager', ['type' => 'snapshot'])
            ->args([
                service('sonata.page.manager.snapshot'),
                service('sonata.page.transformer'),
            ])

        ->set('sonata.page.decorator_strategy', DecoratorStrategy::class)
            ->args([
                abstract_arg('ignore routes'),
                abstract_arg('ignore route patterns'),
                abstract_arg('ignore uri patterns'),
            ])

        ->set('sonata.page.router.request_context', RequestContext::class)
            ->factory([
                service('sonata.page.site.selector'),
                'getRequestContext',
            ])

        ->set('sonata.page.router', CmsPageRouter::class)
            ->public()
            ->args([
                service('router.request_context'),
                service('sonata.page.cms_manager_selector'),
                service('sonata.page.site.selector'),
                service('router.default'),
            ])

        ->set('sonata.page.route.page.generator', RoutePageGenerator::class)
            ->public()
            ->args([
                service('router.default'),
                service('sonata.page.manager.page'),
                service('sonata.page.decorator_strategy'),
                service('sonata.page.kernel.exception_listener'),
            ])

        ->set('sonata.page.template_manager', TemplateManager::class)
            ->public()
            ->args([
                service('twig'),
                abstract_arg('default parameters'),
            ])

        ->set('sonata.page.page_service_manager', PageServiceManager::class)
            ->public()

        ->set('sonata.page.service.default', DefaultPageService::class)
            ->public()
            ->tag('sonata.page')
            ->args([
                'Default',
                service('sonata.page.template_manager'),
                service('sonata.seo.page'),
            ])

        ->alias(TemplateManagerInterface::class, 'sonata.page.template_manager')

        ->alias(SiteSelectorInterface::class, 'sonata.page.site.selector');
};
