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

use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RequestContext;

/**
 * BaseSiteSelector.
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
     * @var SiteInterface|null
     */
    protected $site;

    /**
     * @param SiteManagerInterface       $siteManager       A site manager instance
     * @param DecoratorStrategyInterface $decoratorStrategy A decorator strategy instance
     * @param SeoPageInterface           $seoPage           A SEO page instance
     */
    public function __construct(SiteManagerInterface $siteManager, DecoratorStrategyInterface $decoratorStrategy, SeoPageInterface $seoPage)
    {
        $this->siteManager = $siteManager;
        $this->decoratorStrategy = $decoratorStrategy;
        $this->seoPage = $seoPage;
    }

    public function retrieve()
    {
        return $this->site;
    }

    public function getRequestContext()
    {
        return new RequestContext();
    }

    public function onKernelRequestRedirect(RequestEvent $event): void
    {
    }

    /**
     * @psalm-suppress UndefinedMethod
     */
    final public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->decoratorStrategy->isRouteUriDecorable($event->getRequest()->getPathInfo())) {
            return;
        }

        $this->handleKernelRequest($event);

        // TODO: Simplify when dropping support for Symfony < 5.3.
        // @phpstan-ignore-next-line
        $isMainRequest = method_exists($event, 'isMainRequest') ? $event->isMainRequest() : $event->isMasterRequest();

        if ($isMainRequest && null !== $this->site) {
            $title = $this->site->getTitle();
            if (null !== $title) {
                $this->seoPage->setTitle($title);
            }

            $metaDescription = $this->site->getMetaDescription();
            if (null !== $metaDescription) {
                $this->seoPage->addMeta('name', 'description', $metaDescription);
            }

            $metaKeywords = $this->site->getMetaKeywords();
            if (null !== $metaKeywords) {
                $this->seoPage->addMeta('name', 'keywords', $metaKeywords);
            }
        }
    }

    /**
     * @return SiteInterface[]
     */
    protected function getSites(Request $request)
    {
        // sort by isDefault DESC in order to have default site in first position
        // which will be used if no site found for the current request
        return $this->siteManager->findBy([
            'host' => [$request->getHost(), 'localhost'],
            'enabled' => true,
        ], [
            'isDefault' => 'DESC',
        ]);
    }

    /**
     * Returns TRUE whether the given site matches the given request.
     *
     * @param SiteInterface $site    A site instance
     * @param Request       $request A request instance
     *
     * @return string|false
     */
    protected function matchRequest(SiteInterface $site, Request $request)
    {
        $results = [];

        // we read the value from the attribute to handle fragment support
        $requestPathInfo = $request->get('pathInfo', $request->getPathInfo());

        if (0 === preg_match(sprintf('@^(%s)(/.*|$)@', $site->getRelativePath() ?? ''), $requestPathInfo, $results)) {
            return false;
        }

        return '' !== $results[2] ? $results[2] : '/';
    }

    /**
     * Gets the preferred site based on the given request.
     *
     * @param array<SiteInterface> $sites   An array of enabled sites
     * @param Request              $request A request instance
     *
     * @return SiteInterface|null
     */
    protected function getPreferredSite(array $sites, Request $request)
    {
        if (0 === \count($sites)) {
            return null;
        }

        $sitesLocales = [];

        foreach ($sites as $site) {
            $locale = $site->getLocale();

            if (null !== $locale) {
                $sitesLocales[] = $locale;
            }
        }

        $language = $request->getPreferredLanguage($sitesLocales);
        $host = $request->getHost();

        foreach ($sites as $site) {
            if (\in_array($site->getHost(), ['localhost', $host], true) && $language === $site->getLocale()) {
                return $site;
            }
        }

        return reset($sites);
    }

    /**
     * @return void
     */
    abstract protected function handleKernelRequest(RequestEvent $event);
}
