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

use Sonata\SeoBundle\Seo\SeoPageInterface;

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

    protected $decoratorStrategy;

    protected $seoPage;

    /**
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsSelector
     * @param \Sonata\PageBundle\Site\SiteSelectorInterface $siteSelector
     * @param \Sonata\PageBundle\CmsManager\DecoratorStrategyInterface $decoratorStrategy
     * @param \Sonata\SeoBundle\Seo\SeoPageInterface $seoPage
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, SiteSelectorInterface $siteSelector, DecoratorStrategyInterface $decoratorStrategy, SeoPageInterface $seoPage)
    {
        $this->cmsSelector       = $cmsSelector;
        $this->siteSelector      = $siteSelector;
        $this->decoratorStrategy = $decoratorStrategy;
        $this->seoPage           = $seoPage;
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
            throw new InternalErrorException('No CMS Manager available');
        }

        $routeName = $event->getRequest()->get('_route');

        if ($routeName == 'page_slug') { // true cms page
            return;
        }

        if (!$this->decoratorStrategy->isRouteNameDecorable($routeName) || !$this->decoratorStrategy->isRouteUriDecorable($event->getRequest()->getRequestUri())) {
            return;
        }

        $site = $this->siteSelector->retrieve();

        if (!$site) {
            throw new InternalErrorException('No site available for the current request');
        }

        try {
            $page = $cms->getPageByRouteName($site, $routeName);
            $cms->setCurrentPage($page);

            $this->seoPage->setTitle($page->getName());

            if ($page->getMetaDescription()) {
                $this->seoPage->addMeta('name', 'description', $page->getMetaDescription());
            }

            if ($page->getMetaKeyword()) {
                $this->seoPage->addMeta('name', 'keywords', $page->getMetaKeyword());
            }

            $this->seoPage->addMeta('property', 'og:type', 'article');

        } catch (PageNotFoundException $e) {
            return;
        }
    }
}