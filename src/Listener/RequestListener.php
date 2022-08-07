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

namespace Sonata\PageBundle\Listener;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class RequestListener
{
    private CmsManagerSelectorInterface $cmsSelector;

    private SiteSelectorInterface $siteSelector;

    private DecoratorStrategyInterface $decoratorStrategy;

    /**
     * @param CmsManagerSelectorInterface $cmsSelector       Cms manager selector
     * @param SiteSelectorInterface       $siteSelector      Site selector
     * @param DecoratorStrategyInterface  $decoratorStrategy Decorator strategy
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, SiteSelectorInterface $siteSelector, DecoratorStrategyInterface $decoratorStrategy)
    {
        $this->cmsSelector = $cmsSelector;
        $this->siteSelector = $siteSelector;
        $this->decoratorStrategy = $decoratorStrategy;
    }

    /**
     * Filter the `core.request` event to decorated the action.
     *
     * @throws InternalErrorException
     * @throws PageNotFoundException
     */
    public function onCoreRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $cms = $this->cmsSelector->retrieve();

        // true cms page
        if (PageInterface::PAGE_ROUTE_CMS_NAME === $request->get('_route')) {
            return;
        }

        if (!$this->decoratorStrategy->isRequestDecorable($request)) {
            return;
        }

        $site = $this->siteSelector->retrieve();

        if (null === $site) {
            throw new InternalErrorException('No site available for the current request with uri '.htmlspecialchars($request->getUri(), \ENT_QUOTES));
        }

        $locale = $site->getLocale();

        if (null !== $locale && $locale !== $request->get('_locale')) {
            throw new PageNotFoundException(sprintf('Invalid locale - site.locale=%s - request._locale=%s', $locale, $request->get('_locale')));
        }

        try {
            $page = $cms->getPageByRouteName($site, $request->get('_route'));

            if (!$page->getEnabled() && !$this->cmsSelector->isEditor()) {
                throw new PageNotFoundException(sprintf('The page is not enabled : id=%s', $page->getId() ?? ''));
            }

            $cms->setCurrentPage($page);
        } catch (PageNotFoundException $e) {
            return;
        }
    }
}
