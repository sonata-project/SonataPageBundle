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

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 */
class RequestListener
{
    protected $cmsSelector;

    protected $siteSelector;

    protected $templating;

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsSelector
     * @param \Sonata\PageBundle\Site\SiteSelectorInterface $siteSelector
     * @param \Symfony\Component\Templating\EngineInterface $templating
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, SiteSelectorInterface $siteSelector, EngineInterface $templating)
    {
        $this->cmsSelector = $cmsSelector;
        $this->siteSelector = $siteSelector;
        $this->templating = $templating;
    }

    /**
     * Filter the `core.request` event to decorated the action
     *
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @return
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $cms = $this->cmsSelector->retrieve();

        if (!$cms) {
            return;
        }

        $routeName = $event->getRequest()->get('_route');

        if ($routeName == 'page_slug') { // true cms page
            return;
        }

        if ($cms->isRouteNameDecorable($routeName) && $cms->isRouteUriDecorable($event->getRequest()->getRequestUri())) {
            $site = $this->siteSelector->retrieve();

            if (!$site) {
                $event->setResponse(new Response($this->templating->render('SonataPageBundle:Site:no_site_enabled.html.twig'), 500));

                return;
            }

            $page = $cms->getPageByRouteName($site, $routeName);

            if ($page) {
                $cms->setCurrentPage($page);
            }
        }
    }
}