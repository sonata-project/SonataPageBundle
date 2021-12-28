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

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * HostByLocaleSiteSelector.
 *
 * @author Rémi Marseille <marseille@ekino.com>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class HostByLocaleSiteSelector extends BaseSiteSelector
{
    public function handleKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $enabledSites = [];

        foreach ($this->getSites($request) as $site) {
            if (!$site->isEnabled()) {
                continue;
            }

            $enabledSites[] = $site;

            if (!$site->isLocalhost()) {
                break;
            }
        }

        $this->site = $this->getPreferredSite($enabledSites, $request);

        if ($this->site && $this->site->getLocale()) {
            $request->attributes->set('_locale', $this->site->getLocale());
        }
    }
}
