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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;

use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;

/**
 * BaseSiteSelector
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseSiteSelector implements SiteSelectorInterface
{
    /**
     * @var SiteManagerInterface
     */
    protected $siteManager;

    /**
     * @var DecoratorStrategyInterface
     */
    protected $decoratorStrategy;

    /**
     * @var SeoPageInterface
     */
    protected $seoPage;

    /**
     * @var SiteInterface
     */
    protected $site;

    /**
     * @param SiteManagerInterface       $siteManager       A site manager instance
     * @param DecoratorStrategyInterface $decoratorStrategy A decorator strategy instance
     * @param SeoPageInterface           $seoPage           A SEO page instance
     */
    public function __construct(SiteManagerInterface $siteManager, DecoratorStrategyInterface $decoratorStrategy, SeoPageInterface $seoPage)
    {
        $this->siteManager       = $siteManager;
        $this->decoratorStrategy = $decoratorStrategy;
        $this->seoPage           = $seoPage;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve()
    {
        return $this->site;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestContext()
    {
        return new RequestContext();
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelRequestRedirect(GetResponseEvent $event)
    {
    }

    /**
     * {@inheritdoc}
     */
    final public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$this->decoratorStrategy->isRouteUriDecorable($event->getRequest()->getPathInfo())) {
            return;
        }

        $this->handleKernelRequest($event);

        if ($this->site) {
            if ($this->site->getTitle()) {
                $this->seoPage->setTitle($this->site->getTitle());
            }

            if ($this->site->getMetaDescription()) {
                $this->seoPage->addMeta('name', 'description', $this->site->getMetaDescription());
            }

            if ($this->site->getMetaKeywords()) {
                $this->seoPage->addMeta('name', 'keywords', $this->site->getMetaKeywords());
            }
        }
    }

    /**
     * @param Request $request
     *
     * @return SiteInterface[]
     */
    protected function getSites(Request $request)
    {
        // sort by isDefault DESC in order to have default site in first position
        // which will be used if no site found for the current request
        return $this->siteManager->findBy(array(
            'host'    => array($request->getHost(), 'localhost'),
            'enabled' => true,
        ), array(
            'isDefault' => 'DESC',
        ));
    }

    /**
     * Returns TRUE whether the given site matches the given request
     *
     * @param SiteInterface $site    A site instance
     * @param Request       $request A request instance
     *
     * @return string|boolean FALSE whether the site does not match
     */
    protected function matchRequest(SiteInterface $site, Request $request)
    {
        $results = array();

        // we read the value from the attribute to handle fragment support
        $requestPathInfo = $request->get('pathInfo', $request->getPathInfo());

        if (!preg_match(sprintf('@^(%s)(/.*|$)@', $site->getRelativePath()), $requestPathInfo, $results)) {
            return false;
        }

        return $results[2];
    }

    /**
     * Gets the preferred site based on the given request
     *
     * @param array   $sites   An array of enabled sites
     * @param Request $request A request instance
     *
     * @return SiteInterface|null
     */
    protected function getPreferredSite(array $sites, Request $request)
    {
        if (count($sites) === 0) {
            return null;
        }

        $sitesLocales = array_map(function(SiteInterface $site) {
            return $site->getLocale();
        }, $sites);

        $language = $request->getPreferredLanguage($sitesLocales);
        $host     = $request->getHost();

        foreach ($sites as $site) {
            if (in_array($site->getHost(), array('localhost', $host)) && $language === $site->getLocale()) {
                return $site;
            }
        }

        return reset($sites);
    }

    /**
     * @abstract
     *
     * @param GetResponseEvent $event
     *
     * @return void
     */
    abstract protected function handleKernelRequest(GetResponseEvent $event);
}
