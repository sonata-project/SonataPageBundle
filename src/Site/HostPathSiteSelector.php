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

use Sonata\PageBundle\Request\SiteRequestContext;
use Sonata\PageBundle\Request\SiteRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RequestContext;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class HostPathSiteSelector extends BaseSiteSelector
{
    public function handleKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request instanceof SiteRequestInterface) {
            throw new \RuntimeException('You must configure runtime on your composer.json in order to use "Host path" strategy, take a look on page bundle multiside doc.');
        }

        $defaultSite = null;
        $pathInfo = '/';

        foreach ($this->getSites($request) as $site) {
            if (!$site->isEnabled()) {
                continue;
            }

            if (null === $this->site && $site->getIsDefault()) {
                $defaultSite = $site;
            }

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
            $request->setPathInfo($pathInfo);
        }

        // no valid site, but on there is a default site for the current request
        if (null === $this->site && null !== $defaultSite) {
            $url = $defaultSite->getUrl();
            \assert(null !== $url);

            $event->setResponse(new RedirectResponse($url, 301));
        } elseif (null !== $this->site && null !== $this->site->getLocale()) {
            $request->attributes->set('_locale', $this->site->getLocale());
        }
    }

    public function onKernelRequestRedirect(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (null === $this->site) {
            return;
        }

        if ('Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction' === $request->get('_controller')) {
            $request->attributes->set('path', $this->site->getRelativePath().$request->attributes->get('path'));
        }
    }

    public function getRequestContext(): RequestContext
    {
        return new SiteRequestContext($this);
    }
}
