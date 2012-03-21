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

use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;

class HostSiteSelector extends BaseSiteSelector
{
    /**
     * {@inheritdoc}
     */
    public function handleKernelRequest(GetResponseEvent $event)
    {
        $now = new \DateTime;
        foreach ($this->getSites() as $site) {
            if ($site->getEnabledFrom()->format('U') > $now->format('U')) {
                continue;
            }

            if ($now->format('U') > $site->getEnabledTo()->format('U') ) {
                continue;
            }

            $this->site = $site;

            if ($this->site->getHost() != 'localhost') {
                break;
            }
        }

        if ($this->site && $this->site->getLocale()) {
            $event->getRequest()->attributes->get('_locale', $this->site->getLocale());
        }
    }
}