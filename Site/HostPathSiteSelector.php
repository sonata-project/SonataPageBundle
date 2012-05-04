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

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
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
        if (!$event->getRequest() instanceof SiteRequestInterface) {
            throw new \RuntimeException('You must change the main Request object in the front controller (app.php) in order to use the `host_with_path` strategy');
        }

        $now         = new \DateTime;
        $defaultSite = false;
        foreach ($this->getSites() as $site) {
            if ($site->getEnabledFrom()->format('U') > $now->format('U')) {
                continue;
            }

            if ($now->format('U') > $site->getEnabledTo()->format('U')) {
                continue;
            }

            $results = array();

            if (!$this->site && $site->getIsDefault()) {
                $defaultSite = $site;
            }

            if (!preg_match(sprintf('@^(%s)(.*|)@', $site->getRelativePath()), $event->getRequest()->getPathInfo(), $results)) {
                continue;
            }

            $pathInfo = $results[2];

            $this->site = $site;

            if ($this->site->getHost() != 'localhost') {
                break;
            }
        }

        if ($this->site) {
            $event->getRequest()->setPathInfo($pathInfo ?: '/');
        }

        // no valid site, but on there is a default site for the current request
        if (!$this->site && $defaultSite) {
            $event->setResponse(new RedirectResponse($defaultSite->getUrl(), 301));
        } else if ($this->site && $this->site->getLocale()) {
            $event->getRequest()->attributes->set('_locale', $this->site->getLocale());
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