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
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Model\PageInterface;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RequestListener
{
    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    /**
     * @var SiteSelectorInterface
     */
    protected $siteSelector;

    /**
     * @var DecoratorStrategyInterface
     */
    protected $decoratorStrategy;

    /**
     * Constructor
     *
     * @param CmsManagerSelectorInterface $cmsSelector       Cms manager selector
     * @param SiteSelectorInterface       $siteSelector      Site selector
     * @param DecoratorStrategyInterface  $decoratorStrategy Decorator strategy
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, SiteSelectorInterface $siteSelector, DecoratorStrategyInterface $decoratorStrategy)
    {
        $this->cmsSelector       = $cmsSelector;
        $this->siteSelector      = $siteSelector;
        $this->decoratorStrategy = $decoratorStrategy;
    }

    /**
     * Filter the `core.request` event to decorated the action
     *
     * @param GetResponseEvent $event
     *
     * @return void
     *
     * @throws InternalErrorException
     * @throws PageNotFoundException
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $cms = $this->cmsSelector->retrieve();
        if (!$cms) {
            throw new InternalErrorException('No CMS Manager available');
        }

        // true cms page
        if ($request->get('_route') == PageInterface::PAGE_ROUTE_CMS_NAME) {
            return;
        }

        if (!$this->decoratorStrategy->isRequestDecorable($request)) {
            return;
        }

        $site = $this->siteSelector->retrieve();

        if (!$site) {
            throw new InternalErrorException('No site available for the current request with uri '.htmlspecialchars($request->getUri(), ENT_QUOTES));
        }

        if ($site->getLocale() && $site->getLocale() != $request->get('_locale')) {
            throw new PageNotFoundException(sprintf('Invalid locale - site.locale=%s - request._locale=%s', $site->getLocale(), $request->get('_locale')));
        }

        try {
            $page = $cms->getPageByRouteName($site, $request->get('_route'));

            if (!$page->getEnabled() && !$this->cmsSelector->isEditor()) {
                throw new PageNotFoundException(sprintf('The page is not enabled : id=%s', $page->getId()));
            }

            $cms->setCurrentPage($page);

        } catch (PageNotFoundException $e) {
            return;
        }
    }
}
