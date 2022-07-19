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

namespace Sonata\PageBundle\Listener;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Twig\Environment;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ResponseListener
{
    private CmsManagerSelectorInterface $cmsSelector;

    private PageServiceManagerInterface $pageServiceManager;

    private DecoratorStrategyInterface $decoratorStrategy;

    private Environment $twig;

    private bool $skipRedirection;

    /**
     * @param CmsManagerSelectorInterface $cmsSelector        CMS manager selector
     * @param PageServiceManagerInterface $pageServiceManager Page service manager
     * @param DecoratorStrategyInterface  $decoratorStrategy  Decorator strategy
     * @param Environment                 $twig               Twig engine
     * @param bool                        $skipRedirection    To skip the redirection by configuration
     */
    public function __construct(
        CmsManagerSelectorInterface $cmsSelector,
        PageServiceManagerInterface $pageServiceManager,
        DecoratorStrategyInterface $decoratorStrategy,
        Environment $twig,
        bool $skipRedirection
    ) {
        $this->cmsSelector = $cmsSelector;
        $this->pageServiceManager = $pageServiceManager;
        $this->decoratorStrategy = $decoratorStrategy;
        $this->twig = $twig;
        $this->skipRedirection = $skipRedirection;
    }

    /**
     * Filter the `core.response` event to decorate the action.
     *
     * @throws InternalErrorException
     */
    public function onCoreResponse(FilterResponseEvent $event): void
    {
        $cms = $this->cmsSelector->retrieve();

        $response = $event->getResponse();
        $request = $event->getRequest();

        if ($this->cmsSelector->isEditor()) {
            $response->setPrivate();

            if (!$request->cookies->has('sonata_page_is_editor')) {
                $response->headers->setCookie(Cookie::create('sonata_page_is_editor', '1'));
            }
        }

        $page = $cms->getCurrentPage();

        // display a validation page before redirecting, so the editor can edit the current page
        if (
            $page && $response->isRedirection() &&
            $this->cmsSelector->isEditor() &&
            !$request->get('_sonata_page_skip') &&
            !$this->skipRedirection
        ) {
            $response = new Response($this->twig->render('@SonataPage/Page/redirect.html.twig', [
                'response' => $response,
                'page' => $page,
            ]));

            $response->setPrivate();

            $event->setResponse($response);

            return;
        }

        if (!$this->decoratorStrategy->isDecorable($event->getRequest(), $event->getRequestType(), $response)) {
            return;
        }

        if (!$this->cmsSelector->isEditor() && $request->cookies->has('sonata_page_is_editor')) {
            $response->headers->clearCookie('sonata_page_is_editor');
        }

        if (!$page) {
            throw new InternalErrorException('No page instance available for the url, run the sonata:page:update-core-routes and sonata:page:create-snapshots commands');
        }

        // only decorate hybrid page or page with decorate = true
        if (!$page->isHybrid() || !$page->getDecorate()) {
            return;
        }

        $parameters = [
            'content' => $response->getContent(),
        ];

        $response = $this->pageServiceManager->execute($page, $request, $parameters, $response);

        $event->setResponse($response);
    }
}
