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

use Sonata\PageBundle\Request\SiteRequestInterface;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * HostPathByLocaleSiteSelector
 *
 * @author Rémi Marseille <marseille@ekino.com>
 */
class HostPathByLocaleSiteSelector extends HostPathSiteSelector
{
    /**
     * {@inheritdoc}
     */
    public function handleKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request instanceof SiteRequestInterface) {
            throw new \RuntimeException('You must change the main Request object in the front controller (app.php) in order to use the `host_with_path_by_locale` strategy');
        }

        $enabledSites = array();
        $pathInfo     = null;

        foreach ($this->getSites($request) as $site) {
            if (!$site->isEnabled()) {
                continue;
            }

            $enabledSites[] = $site;

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

        // no valid site, but try to find a default site for the current request
        if (!$this->site && count($enabledSites) > 0) {
            $defaultSite = $this->getPreferredSite($enabledSites, $request);

            $event->setResponse(new RedirectResponse($defaultSite->getUrl()));
        } elseif ($this->site && $this->site->getLocale()) {
            $request->attributes->set('_locale', $this->site->getLocale());
        }
    }
}
