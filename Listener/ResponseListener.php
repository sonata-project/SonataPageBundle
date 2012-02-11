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

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 */
class ResponseListener
{
    protected $cmsSelector;

    protected $pageRenderer;

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsSelector
     * @param \Sonata\PageBundle\CmsManager\PageRendererInterface $pageRenderer
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, PageRendererInterface $pageRenderer)
    {
        $this->cmsSelector = $cmsSelector;
        $this->pageRenderer = $pageRenderer;
    }

    /**
     * Filter the `core.response` event to decorated the action
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     * @return void
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        $cms = $this->cmsSelector->retrieve();

        $response = $event->getResponse();

        if (!$cms->isDecorable($event->getRequest(), $event->getRequestType(), $response)) {
            return;
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
    }
}