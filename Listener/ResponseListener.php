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

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 */
class ResponseListener
{
    protected $cmsSelector;

    protected $siteSelector;

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsSelector
     * @param \Sonata\PageBundle\Site\SiteSelectorInterface $siteSelector
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, SiteSelectorInterface $siteSelector)
    {
        $this->cmsSelector = $cmsSelector;
        $this->siteSelector = $siteSelector;
    }

    /**
     * filter the `core.response` event to decorated the action
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
     * @return void
     */
    public function onCoreResponse(FilterResponseEvent $event)
    {
        $cmsManager  = $this->cmsSelector->retrieve();

        if (!$cmsManager) {
            return;
        }

        $response    = $event->getResponse();
        $request     = $event->getRequest();

        if (!$cmsManager->isDecorable($request, $event->getRequestType(), $response)) {
            return;
        }

        $site = $this->siteSelector->retrieve();

        if (!($page = $cmsManager->getCurrentPage())) {
            $routeName = $request->get('_route');

            if ($routeName == 'page_slug') { // true cms page
                return;
            }

            $page = $cmsManager->getPageByRouteName($site, $routeName);
//                    $this->getLogger()->crit(sprintf('[page:getCurrentPage] no page available for route : %s', $routeName));
        }

        // only decorate hybrid page and page with decorate = true
        if (!$page || !$page->isHybrid() || !$page->getDecorate()) {
            return;
        }

        $cmsManager->setCurrentPage($page);

        $event->setResponse(
            $cmsManager->renderPage(
                $page,
                array('content' => $response->getContent()),
                $response
            )
        );
    }
}