<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\CmsManager;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Model\BlockManagerInterface;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Exception\InternalErrorException;

use Sonata\AdminBundle\Admin\AdminInterface;

abstract class BaseCmsPageManager implements CmsManagerInterface
{
    protected $httpErrorCodes;

    protected $pages = array();

    protected $pageReferences = array();

    protected $currentPage;

    protected $options = array();

    protected $blocks = array();

    /**
     * @param array $httpErrorCodes
     */
    public function __construct(array $httpErrorCodes = array())
    {
        $this->httpErrorCodes      = $httpErrorCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpErrorCodes()
    {
        return $this->httpErrorCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasErrorCode($statusCode)
    {
        return array_key_exists($statusCode, $this->httpErrorCodes);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCodePage(SiteInterface $site, $statusCode)
    {
        if (!$this->hasErrorCode($statusCode)) {
            throw new InternalErrorException(sprintf('There is not page configured to handle the status code %d', $statusCode));
        }

        return $this->getPageByName($site, $this->httpErrorCodes[$statusCode]);
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorable(Request $request, $requestType, Response $response)
    {
        if ($requestType != HttpKernelInterface::MASTER_REQUEST) {
            return false;
        }

        if (($response->headers->get('Content-Type') ?: 'text/html') != 'text/html') {
            return false;
        }

        if ($response->getStatusCode() != 200) {
            return false;
        }

        if ($request->headers->get('x-requested-with') == 'XMLHttpRequest') {
            return false;
        }

        if ($response->headers->get('x-sonata-page-decorable', true) == false) {
            return false;
        }

        return $this->isRouteNameDecorable($request->get('_route')) && $this->isRouteUriDecorable($request->getRequestUri());
    }

    /**
     * {@inheritdoc}
     */
    public function isRouteNameDecorable($routeName)
    {
        if (!$routeName) {
            return false;
        }

        foreach ($this->getOption('ignore_routes', array()) as $route) {
            if ($routeName == $route) {
                return false;
            }
        }

        foreach ($this->getOption('ignore_route_patterns', array()) as $routePattern) {
            if (preg_match($routePattern, $routeName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isRouteUriDecorable($uri)
    {
        foreach ($this->getOption('ignore_uri_patterns', array()) as $uriPattern) {
            if (preg_match($uriPattern, $uri)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrentPage(PageInterface $page)
    {
        $this->currentPage = $page;
    }

    /**
     * @param array $options
     * @return void
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @param $name
     * @param null $default
     * @return null
     */
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * @param $pageReferences
     * @return void
     */
    public function setPageReferences($pageReferences)
    {
        $this->pageReferences = $pageReferences;
    }

    /**
     * @return array
     */
    public function getPageReferences()
    {
        return $this->pageReferences;
    }

    /**
     * @param $blocks
     * @return void
     */
    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlocks()
    {
        return $this->blocks;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageByUrl(SiteInterface $site, $url)
    {
        return $this->getPageBy($site, 'url', $url);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageByRouteName(SiteInterface $site, $routeName, $create = true)
    {
        return $this->getPageBy($site, 'routeName', $routeName);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageByName(SiteInterface $site, $name)
    {
        return $this->getPageBy($site, 'name', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getPageById($id)
    {
        return $this->getPageBy(null, 'id', $id);
    }

    /**
     * @param null|\Sonata\PageBundle\Model\SiteInterface $site
     * @param $fieldName
     * @param $value
     * @return void
     */
    abstract protected function getPageBy(SiteInterface $site = null, $fieldName, $value);
}
