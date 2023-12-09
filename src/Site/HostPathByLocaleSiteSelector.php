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

namespace Sonata\PageBundle\Site;

use Sonata\PageBundle\Request\SiteRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @author Rémi Marseille <marseille@ekino.com>
 */
final class HostPathByLocaleSiteSelector extends HostPathSiteSelector
{
    public function handleKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request instanceof SiteRequestInterface) {
            throw new \RuntimeException('You must configure runtime on your composer.json in order to use "Host path by locale" strategy, take a look on page bundle multiside doc.');
        }

        $enabledSites = [];
        $pathInfo = null;

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
            $pathInfo = $match;

            if (!$this->site->isLocalhost()) {
                break;
            }
        }

        if (null !== $this->site) {
            $request->setPathInfo($pathInfo ?? '/');
        }

        // no valid site, but try to find a default site for the current request
        if (null === $this->site && \count($enabledSites) > 0) {
            $defaultSite = $this->getPreferredSite($enabledSites, $request);
            \assert(null !== $defaultSite);

            $url = $defaultSite->getUrl();
            \assert(null !== $url);

            $event->setResponse(new RedirectResponse($url));
        } elseif (null !== $this->site && null !== $this->site->getLocale()) {
            $request->attributes->set('_locale', $this->site->getLocale());
        }
    }
}
