<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Route;

use Symfony\Cmf\Component\Routing\ChainedRouterInterface;
use Symfony\Cmf\Component\Routing\VersatileGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Site\SiteSelectorInterface;

class CmsPageRouter implements ChainedRouterInterface
{
    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    /**
     * @var SiteSelectorInterface
     */
    protected $siteSelector;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param CmsManagerSelectorInterface $cmsSelector  Cms manager selector
     * @param SiteSelectorInterface       $siteSelector Sites selector
     * @param RouterInterface             $router       Router for hybrid pages
     */
    public function __construct(CmsManagerSelectorInterface $cmsSelector, SiteSelectorInterface $siteSelector, RouterInterface $router)
    {
        $this->cmsSelector  = $cmsSelector;
        $this->siteSelector = $siteSelector;
        $this->router       = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        if (is_string($name) && !$this->isPageAlias($name) && !$this->isPageSlug($name)) {
            return false;
        }

        if (is_object($name) && !($name instanceof PageInterface)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        try {
            $url = false;

            if ($this->isPageAlias($name)) {
                $name = $this->getPageByPageAlias($name);
            }

            if ($name instanceof PageInterface) {
                $url = $this->generateFromPage($name, $parameters, $referenceType);
            }

            if ($this->isPageSlug($name)) {
                $url = $this->generateFromPageSlug($parameters, $referenceType);
            }

            if ($url === false) {
                throw new RouteNotFoundException('The Sonata CmsPageRouter cannot find url');
            }

        } catch (PageNotFoundException $exception) {
            throw new RouteNotFoundException('The Sonata CmsPageRouter cannot find page');
        }

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteDebugMessage($name, array $parameters = array())
    {
        if ($this->router instanceof VersatileGeneratorInterface) {
            return $this->router->getRouteDebugMessage($name, $parameters);
        }

        return "Route '$name' not found";
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathinfo)
    {
        $cms = $this->cmsSelector->retrieve();
        $site = $this->siteSelector->retrieve();

        if (!$cms instanceof CmsManagerInterface) {
            throw new ResourceNotFoundException("No CmsManager defined");
        }

        if (!$site instanceof SiteInterface) {
            throw new ResourceNotFoundException("No site defined");
        }

        try {
            $page = $cms->getPageByUrl($site, $pathinfo);
        } catch (PageNotFoundException $e) {
            throw new ResourceNotFoundException($pathinfo, 0, $e);
        }

        if (!$page || !$page->isCms()) {
            throw new ResourceNotFoundException($pathinfo);
        }

        if (!$page->getEnabled() && !$this->cmsSelector->isEditor()) {
            throw new ResourceNotFoundException($pathinfo);
        }

        $cms->setCurrentPage($page);

        return array (
            '_controller' => 'sonata.page.page_service_manager:execute',
            '_route'      => PageInterface::PAGE_ROUTE_CMS_NAME,
            'page'        => $page,
            'path'        => $pathinfo,
            'params'      => array()
        );
    }

    /**
     * Generates an URL from a Page object
     *
     * @param PageInterface $page          Page object
     * @param array         $parameters    An array of parameters
     * @param bool|string   $referenceType The type of reference to be generated (one of the constants)
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function generateFromPage(PageInterface $page, array $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        // hybrid pages use, by definition, the default routing mechanism
        if ($page->isHybrid()) {
            return $this->router->generate($page->getRouteName(), $parameters, $referenceType);
        }

        $url = $this->getUrlFromPage($page);

        if ($url === false) {
            throw new \RuntimeException(sprintf('Page "%d" has no url or customUrl.', $page->getId()));
        }

        return $this->decorateUrl($url, $parameters, $referenceType);
    }

    /**
     * Generates an URL for a page slug
     *
     * @param array       $parameters    An array of parameters
     * @param bool|string $referenceType The type of reference to be generated (one of the constants)
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function generateFromPageSlug(array $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (!isset($parameters['path'])) {
            throw new \RuntimeException('Please provide a `path` parameters');
        }

        $url = $parameters['path'];
        unset($parameters['path']);

        return $this->decorateUrl($url, $parameters, $referenceType);
    }

    /**
     * Decorates an URL with url context and query
     *
     * @param string      $url           Relative URL
     * @param array       $parameters    An array of parameters
     * @param bool|string $referenceType The type of reference to be generated (one of the constants)
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function decorateUrl($url, array $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (!$this->context) {
            throw new \RuntimeException('No context associated to the CmsPageRouter');
        }

        $schemeAuthority = '';
        if ($this->context->getHost() && (self::ABSOLUTE_URL === $referenceType || self::NETWORK_PATH === $referenceType)) {
            $port = '';
            if ('http' === $this->context->getScheme() && 80 != $this->context->getHttpPort()) {
                $port = sprintf(':%s', $this->context->getHttpPort());
            } elseif ('https' === $this->context->getScheme() && 443 != $this->context->getHttpsPort()) {
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

        if (count($parameters) > 0) {
            return sprintf('%s?%s', $url, http_build_query($parameters, '', '&'));
        }

        return $url;
    }
    /**
     * Returns the target path as relative reference from the base path.
     *
     * @param string $basePath   The base path
     * @param string $targetPath The target path
     *
     * @return string The relative target path
     */
    protected function getRelativePath($basePath, $targetPath)
    {
        return UrlGenerator::getRelativePath($basePath, $targetPath);
    }

    /**
     * Retrieves a page object from a page alias
     *
     * @param string $alias
     *
     * @return \Sonata\PageBundle\Model\PageInterface|null
     *
     * @throws PageNotFoundException
     */
    protected function getPageByPageAlias($alias)
    {
        $site = $this->siteSelector->retrieve();
        $page = $this->cmsSelector->retrieve()->getPageByPageAlias($site, $alias);

        return $page;
    }

    /**
     * Returns the Url from a Page object
     *
     * @param PageInterface $page
     *
     * @return string
     */
    protected function getUrlFromPage(PageInterface $page)
    {
        return $page->getCustomUrl() ?: $page->getUrl();
    }

    /**
     * Returns whether this name is a page alias or not
     *
     * @param string $name
     *
     * @return bool
     */
    protected function isPageAlias($name)
    {
        return (is_string($name) && substr($name, 0, 12) === '_page_alias_');
    }

    /**
     * Returns whether this name is a page slug route or not
     *
     * @param string $name
     *
     * @return bool
     */
    protected function isPageSlug($name)
    {
        return (is_string($name) && $name == PageInterface::PAGE_ROUTE_CMS_NAME);
    }
}
