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

use Symfony\Component\Routing\RouterInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\NotificationBundle\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

use Sonata\PageBundle\CmsManager\CmsManagerSelector;
use Sonata\PageBundle\Site\SiteSelectorInterface;

class CmsPageRouter implements RouterInterface
{
    protected $context;

    protected $cmsSelector;

    protected $siteSelector;

    protected $router;

    /**
     * @param CmsManagerSelector $selector
     */
    public function __construct(CmsManagerSelector $cmsSelector, SiteSelectorInterface $siteSelector)
    {
        $this->cmsSelector = $cmsSelector;
        $this->siteSelector = $siteSelector;
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
    public function generate($name, $parameters = array(), $absolute = false)
    {
        if ($name == 'page_slug') {
            if (!isset($parameters['path'])) {
                throw new InvalidParameterException('Please provide a `path` parameters');
            }

            $url = $parameters['path'];
        } else {
            $url = $this->generatePageUrl($name);
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

        return $url;
    }

    /**
     * @param \Sonata\PageBundle\Model\PageInterface $page
     *
     * @return string
     * @throws \RunTimeException
     */
    private function generatePageUrl($page)
    {
        if (!$page instanceof PageInterface) {
            try {
                $page = $this->cmsSelector->retrieve()->getPageByRouteAlias($this->siteSelector->retrieve(), $page);
            } catch (PageNotFoundException $e) {
                return false;
            }
        }

        if ($page->isDynamic()) {
            return false;
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

        try {
            $page = $cms->getPageByUrl($site, $pathinfo);
        } catch(PageNotFoundException $e) {
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
            'params'      => array()
        );
    }
}