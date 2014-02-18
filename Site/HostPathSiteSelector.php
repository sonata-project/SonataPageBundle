<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Site;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Sonata\PageBundle\Request\SiteRequestInterface;
use Sonata\PageBundle\Request\SiteRequestContext;

/**
 * HostPathSiteSelector
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class HostPathSiteSelector extends BaseSiteSelector
{
    /**
     * {@inheritdoc}
     */
    public function handleKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request instanceof SiteRequestInterface) {
            throw new \RuntimeException('You must change the main Request object in the front controller (app.php) in order to use the `host_with_path` strategy');
        }

        $defaultSite = false;
        $pathInfo    = null;

        foreach ($this->getSites($request) as $site) {
            if (!$site->isEnabled()) {
                continue;
            }

            if (!$this->site && $site->getIsDefault()) {
                $defaultSite = $site;
            }

            $match = $this->matchRequest($site, $request);

            if (false === $match) {
                continue;
            }

            $this->site = $site;
            $pathInfo   = $match;

            if (!$this->site->isLocalhost()) {
                break;
            }
        }

        if ($this->site) {
            $request->setPathInfo($pathInfo ?: '/');
        }

        // no valid site, but on there is a default site for the current request
        if (!$this->site && $defaultSite) {
            $event->setResponse(new RedirectResponse($defaultSite->getUrl(), 301));
        } elseif ($this->site && $this->site->getLocale()) {
            $request->attributes->set('_locale', $this->site->getLocale());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelRequestRedirect(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$this->site) {
            return;
        }

        if ('Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction' == $request->get('_controller')) {
            $request->attributes->set('path', $this->site->getRelativePath() . $request->attributes->get('path'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestContext()
    {
        return new SiteRequestContext($this);
    }
}
