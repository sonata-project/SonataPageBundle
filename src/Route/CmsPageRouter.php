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

namespace Sonata\PageBundle\Route;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Request\SiteRequestContextInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final class CmsPageRouter implements ChainedRouterInterface
{
    private RequestContext $context;

    private CmsManagerSelectorInterface $cmsSelector;

    private SiteSelectorInterface $siteSelector;

    private RouterInterface $router;

    public function __construct(
        RequestContext $context,
        CmsManagerSelectorInterface $cmsSelector,
        SiteSelectorInterface $siteSelector,
        RouterInterface $router
    ) {
        $this->context = $context;
        $this->cmsSelector = $cmsSelector;
        $this->siteSelector = $siteSelector;
        $this->router = $router;
    }

    public function setContext(RequestContext $context): void
    {
        $this->context = $context;
    }

    public function getContext(): RequestContext
    {
        return $this->context;
    }

    public function getRouteCollection(): RouteCollection
    {
        return new RouteCollection();
    }

    /**
     * @param mixed $name
     */
    public function supports($name): bool
    {
        if (\is_string($name) && !$this->isPageAlias($name) && !$this->isPageSlug($name)) {
            return false;
        }

        if (\is_object($name) && !($name instanceof PageInterface)) {
            return false;
        }

        return true;
    }

    /**
     * @param string|PageInterface $name
     * @param array<mixed>         $parameters
     * @param int                  $referenceType
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH): string
    {
        try {
            $url = null;

            if (\is_string($name) && $this->isPageAlias($name)) {
                $name = $this->getPageByPageAlias($name);
            }

            if ($name instanceof PageInterface) {
                $url = $this->generateFromPage($name, $parameters, $referenceType);
            }

            if (\is_string($name) && $this->isPageSlug($name)) {
                $url = $this->generateFromPageSlug($parameters, $referenceType);
            }

            if (null === $url) {
                throw new RouteNotFoundException('The Sonata CmsPageRouter cannot find url');
            }
        } catch (PageNotFoundException $exception) {
            throw new RouteNotFoundException('The Sonata CmsPageRouter cannot find page');
        }

        return $url;
    }

    /**
     * @param string               $name
     * @param array<string, mixed> $parameters
     */
    public function getRouteDebugMessage($name, array $parameters = []): string
    {
        if ($this->router instanceof VersatileGeneratorInterface) {
            return $this->router->getRouteDebugMessage($name, $parameters);
        }

        return "Route '$name' not found";
    }

    /**
     * @param string $pathinfo
     *
     * @return array{
     *   _controller: string,
     *   _route: string,
     *   page: PageInterface,
     *   path: string,
     *   params: array<string, mixed>
     * }
     */
    public function match($pathinfo): array
    {
        $cms = $this->cmsSelector->retrieve();
        $site = $this->siteSelector->retrieve();

        if (null === $site) {
            throw new ResourceNotFoundException('No site defined');
        }

        try {
            $page = $cms->getPageByUrl($site, $pathinfo);
        } catch (PageNotFoundException $e) {
            throw new ResourceNotFoundException($pathinfo, 0, $e);
        }

        if (!$page->isCms()) {
            throw new ResourceNotFoundException($pathinfo);
        }

        if (!$page->getEnabled() && !$this->cmsSelector->isEditor()) {
            throw new ResourceNotFoundException($pathinfo);
        }

        $cms->setCurrentPage($page);

        return [
            '_controller' => 'sonata.page.page_service_manager::execute',
            '_route' => PageInterface::PAGE_ROUTE_CMS_NAME,
            'page' => $page,
            'path' => $pathinfo,
            'params' => [],
        ];
    }

    /**
     * Generates an URL from a Page object.
     * We swap site context to make sure the right context is used when generating the url.
     *
     * @param array<string, mixed> $parameters
     *
     * @throws \RuntimeException
     */
    private function generateFromPage(PageInterface $page, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        // hybrid pages use, by definition, the default routing mechanism
        if ($page->isHybrid()) {
            $routeName = $page->getRouteName();

            if (null === $routeName) {
                throw new \RuntimeException('Page is hybrid but has no route name');
            }

            return $this->router->generate($routeName, $parameters, $referenceType);
        }

        $url = $this->getUrlFromPage($page);

        if (null === $url) {
            throw new \RuntimeException(sprintf('Page "%d" has no url or customUrl.', $page->getId() ?? ''));
        }

        if (!$this->context instanceof SiteRequestContextInterface) {
            throw new \RuntimeException('The context must be an instance of SiteRequestContextInterface');
        }

        // Get current site
        $currentSite = $this->context->getSite();
        // Change to new site
        $this->context->setSite($page->getSite());
        // Fetch Url
        $decoratedUrl = $this->decorateUrl($url, $parameters, $referenceType);
        // Swap back to original site
        $this->context->setSite($currentSite);

        return $decoratedUrl;
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws \RuntimeException
     */
    private function generateFromPageSlug(array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        if (!isset($parameters['path'])) {
            throw new \RuntimeException('Please provide a `path` parameters');
        }

        $url = $parameters['path'];
        unset($parameters['path']);

        return $this->decorateUrl($url, $parameters, $referenceType);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws \RuntimeException
     */
    private function decorateUrl(string $url, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        $schemeAuthority = '';
        if (self::ABSOLUTE_URL === $referenceType || self::NETWORK_PATH === $referenceType) {
            $port = '';
            if ('http' === $this->context->getScheme() && 80 !== $this->context->getHttpPort()) {
                $port = sprintf(':%s', $this->context->getHttpPort());
            } elseif ('https' === $this->context->getScheme() && 443 !== $this->context->getHttpsPort()) {
                $port = sprintf(':%s', $this->context->getHttpsPort());
            }

            $schemeAuthority = self::NETWORK_PATH === $referenceType ? '//' : sprintf('%s://', $this->context->getScheme());
            $schemeAuthority = sprintf('%s%s%s', $schemeAuthority, $this->context->getHost(), $port);
        }

        if (self::RELATIVE_PATH === $referenceType) {
            $url = $this->getRelativePath($this->context->getPathInfo(), $url);
        } else {
            $url = sprintf('%s%s%s', $schemeAuthority, $this->context->getBaseUrl(), $url);
        }

        if (\count($parameters) > 0) {
            return sprintf('%s?%s', $url, http_build_query($parameters, '', '&'));
        }

        return $url;
    }

    private function getRelativePath(string $basePath, string $targetPath): string
    {
        return UrlGenerator::getRelativePath($basePath, $targetPath);
    }

    /**
     * @throws PageNotFoundException
     */
    private function getPageByPageAlias(string $alias): ?PageInterface
    {
        $site = $this->siteSelector->retrieve();

        if (null === $site) {
            return null;
        }

        $page = $this->cmsSelector->retrieve()->getPageByPageAlias($site, $alias);

        return $page;
    }

    private function getUrlFromPage(PageInterface $page): ?string
    {
        return $page->getCustomUrl() ?? $page->getUrl();
    }

    private function isPageAlias(string $name): bool
    {
        return '_page_alias_' === substr($name, 0, 12);
    }

    private function isPageSlug(string $name): bool
    {
        return PageInterface::PAGE_ROUTE_CMS_NAME === $name;
    }
}
