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
     * @param RouterInterface             $router       Router for dynamic pages
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
        if (is_string($name) && substr($name, 0, 12) !== '_page_alias_' && $name !== 'page_slug') {
            return false;
        }

        // a Sonata Page CMS's alias must start by _page_alias_ to avoid to many queries
        if (is_object($name) && !$name instanceof PageInterface ) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($name, $parameters = array(), $absolute = false)
    {
        // Urls for dynamic pages are generated with their route name
        if ($name instanceof PageInterface && $name->isDynamic()) {
            return $this->router->generate($name->getRouteName(), $parameters, $absolute);
        }

        if ($name == 'page_slug') {
            if (!isset($parameters['path'])) {
                throw new \RuntimeException('Please provide a `path` parameters');
            }

            $url = $parameters['path'];

            unset($parameters['path']);
        } elseif ($name instanceof PageInterface) {
            $url = $this->generatePageUrl($name);
        } else {
            throw new \RuntimeException('The route name is not supported');
        }

        if ($url === false) {
            throw new RouteNotFoundException('The Sonata CmsPageRouter cannot generate route');
        }

        if (!$this->context) {
            throw new \RuntimeException('No context associated to the CmsPageRouter');
        }

        $url = sprintf('%s%s', $this->context->getBaseUrl(), $url);

        if ($absolute && $this->context->getHost()) {
            $scheme = $this->context->getScheme();

            $port = '';
            if ('http' === $scheme && 80 != $this->context->getHttpPort()) {
                $port = ':'.$this->context->getHttpPort();
            } elseif ('https' === $scheme && 443 != $this->context->getHttpsPort()) {
                $port = ':'.$this->context->getHttpsPort();
            }

            $url = $scheme.'://'.$this->context->getHost().$port.$url;
        }

        if (count($parameters) > 0) {
            return sprintf('%s?%s', $url, http_build_query($parameters, '', '&'));
        }

        return $url;
    }

    /**
     * @param \Sonata\PageBundle\Model\PageInterface $page
     *
     * @return string
     *
     * @throws \RunTimeException
     */
    private function generatePageUrl(PageInterface $page)
    {
        if (!$page instanceof PageInterface) {
            try {
                $page = $this->cmsSelector->retrieve()->getPageByPageAlias($this->siteSelector->retrieve(), $page);
            } catch (PageNotFoundException $e) {
                return false;
            }
        }

        return $page->getCustomUrl() ?: $page->getUrl();
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

        $cms->setCurrentPage($page);

        return array (
            '_controller' => 'sonata.page.renderer:render',
            '_route'      => 'page_slug',
            'page'        => $page,
            'path'        => $pathinfo,
            'params'      => array()
        );
    }
}