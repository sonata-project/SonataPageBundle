<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Listener;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\CmsManager\PageRendererInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ResponseListener
{
    protected $cmsSelector;

    protected $pageRenderer;

    protected $decoratorStrategy;

    /**
     * @param CmsManagerSelectorInterface $cmsSelector
     * @param PageRendererInterface       $pageRenderer
     * @param DecoratorStrategyInterface  $decoratorStrategy
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, PageRendererInterface $pageRenderer, DecoratorStrategyInterface $decoratorStrategy)
    {
        $this->cmsSelector       = $cmsSelector;
        $this->pageRenderer      = $pageRenderer;
        $this->decoratorStrategy = $decoratorStrategy;
    }

    /**
     * Filter the `core.response` event to decorated the action
     *
     * @param FilterResponseEvent $event
     *
     * @return void
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        $cms = $this->cmsSelector->retrieve();

        $response = $event->getResponse();
        $request  = $event->getRequest();

        if ($this->cmsSelector->isEditor()) {
            $response->setPrivate();

            if (!$request->cookies->has('sonata_page_is_editor')) {
                $response->headers->setCookie(new Cookie('sonata_page_is_editor', 1));
            }
        }

        if (!$this->decoratorStrategy->isDecorable($event->getRequest(), $event->getRequestType(), $response)) {
            return;
        }

        if (!$this->cmsSelector->isEditor() && $request->cookies->has('sonata_page_is_editor')) {
            $response->headers->clearCookie('sonata_page_is_editor');
        }

        $page = $cms->getCurrentPage();

        if (!$page) {
            throw new InternalErrorException('No page instance available for the url, run the sonata:page:update-core-routes and sonata:page:create-snapshots commands');
        }

        // only decorate hybrid page and page with decorate = true
        if (!$page->isHybrid() || !$page->getDecorate()) {
            return;
        }

        $this->pageRenderer->render($page, array('content' => $response->getContent()), $response);

        if (!$this->cmsSelector->isEditor() && $page->isCms()) {
            $response->setTtl($page->getTtl());
        }
    }
}