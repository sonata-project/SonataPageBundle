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

        if ($event->getRequest()->get('_route') == 'page_slug') { // true cms page
            return;
        }

        if (!$this->decoratorStrategy->isRequestDecorable($event->getRequest())) {
            return;
        }

        $site = $this->siteSelector->retrieve();

        if (!$site) {
            throw new InternalErrorException('No site available for the current request');
        }

        if ($site->getLocale() && $site->getLocale() != $event->getRequest()->get('_locale')) {
            throw new PageNotFoundException(sprintf('Invalid locale - site.locale=%s - request._locale=%s', $site->getLocale(), $event->getRequest()->get('_locale')));
        }

        try {
            $page = $cms->getPageByRouteName($site, $event->getRequest()->get('_route'));
            $cms->setCurrentPage($page);

            $this->seoPage->setTitle($page->getName());

            if ($page->getMetaDescription()) {
                $this->seoPage->addMeta('name', 'description', $page->getMetaDescription());
            }

            if ($page->getMetaKeyword()) {
                $this->seoPage->addMeta('name', 'keywords', $page->getMetaKeyword());
            }

            $this->seoPage->addMeta('property', 'og:type', 'article');
            $this->seoPage->addHtmlAttributes('prefix','og: http://ogp.me/ns#');

        } catch (PageNotFoundException $e) {
            return;
        }
    }
}