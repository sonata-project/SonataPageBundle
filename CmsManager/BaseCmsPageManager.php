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

use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Block\BlockServiceInterface;
use Sonata\PageBundle\Cache\CacheInterface;
use Sonata\PageBundle\Cache\CacheElement;
use Sonata\PageBundle\Cache\Invalidation\InvalidationInterface;
use Sonata\PageBundle\Cache\Invalidation\Recorder;

use Sonata\AdminBundle\Admin\AdminInterface;

abstract class BaseCmsPageManager implements CmsManagerInterface
{
    protected $routePages = array();

    protected $currentPage;

    protected $pageLoader;

    protected $options = array();

    protected $blockServices = array();

    protected $logger;

    protected $debug = false;

    protected $cacheServices = array();

    protected $pageManager;

    protected $blocks = array();

    protected $cacheInvalidation;

    protected $recorder;

    protected $defaultTemplatePath = 'SonataPageBundle::layout.html.twig';

    /**
     * filter the `core.response` event to decorated the action
     *
     * @param Event $event
     * @param Response $response
     * @return
     */
    public function onCoreResponse($event)
    {
        $response    = $event->getResponse();
        $requestType = $event->getRequestType();
        $request     = $event->getRequest();

        if ($this->isDecorable($request, $requestType, $response)) {
            $page = $this->defineCurrentPage($request);

            // only decorate hybrid page and page with decorate = true
            if ($page && $page->isHybrid() && $page->getDecorate()) {
                $parameters = array(
                    'content'     => $response->getContent(),
                );

                $response = $this->renderPage($page, $parameters, $response);
            }
        }

        return $response;
    }

    public function addCacheService($name, CacheInterface $cacheManager)
    {
        $this->cacheServices[$name] = $cacheManager;
    }

    public function addBlockService($name, BlockServiceInterface $service)
    {
        $this->blockServices[$name] = $service;

        $service->setManager($this);
    }

    /**
     * return true is the page can be decorate with an outter template
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $requestType
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return bool
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

        $routeName = $request->get('_route');

        return $this->isRouteNameDecorable($routeName) && $this->isRouteUriDecorable($request->getRequestUri());
    }

    /**
     * @param string $routeName
     * @return bool
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
     * @param string $uri
     * @return bool
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

    public function getCreateNewPageDefaultsByName($name)
    {
        $params = $this->getOption('page_defaults', array());

        return isset($params[$name]) ? $params[$name] : array();
    }

    /**
     * Render a specialize block
     *
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param boolean $useCache
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderBlock(BlockInterface $block, PageInterface $page, $useCache = true)
    {
        if ($this->getLogger()) {
            $this->getLogger()->info(sprintf('[cms::renderBlock] block.id=%d, block.type=%s ', $block->getId(), $block->getType()));
        }

        $response = new Response;

        try {
            $service       = $this->getBlockService($block);
            $service->load($block); // load the block

            $cacheManager  = $this->getCacheService($block);
            $cacheElement  = $service->getCacheElement($block);

            $cacheElement->addKey('manager', $this->getCode());

            if ($useCache) {
                if ($cacheManager->has($cacheElement)) {
                    $response = $cacheManager->get($cacheElement);

                    if (!$response instanceof Response) {
                        throw new \RuntimeException('The cache must return a Response object');
                    }

                    return $response;
                }

                if ($this->recorder && $cacheManager->isContextual()) {
                    $this->recorder->reset();
                }
            }

            $response = $service->execute($block, $page, $response);

            if (!$response instanceof Response) {
                throw new \RuntimeException('A block service must return a Response object');
            }

            if ($useCache) {
                $cacheElement->setValue($response);

                if ($this->recorder && $cacheManager->isContextual()) {
                    foreach ($this->recorder->get() as $class => $idx) {
                        if (count($idx) == 0) {
                            continue;
                        }
                        $cacheElement->addContextualKey($class, array_unique($idx));
                    }

                    $this->recorder->reset();
                }

                $cacheManager->set($cacheElement);
            }
        } catch (\Exception $e) {
            if ($this->getLogger()) {
                $this->getLogger()->crit(sprintf('[cms::renderBlock] block.id=%d - error while rendering block - %s', $block->getId(), $e->getMessage()));
            }

            if ($this->getDebug()) {
                throw $e;
            }

            $response->setPrivate();
        }

        return $response;
    }

    /**
     * @param string $name
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param \Sonata\PageBundle\Model\BlockInterface $parentContainer
     * @return string
     */
    public function renderContainer($name, $page = null, BlockInterface $parentContainer = null)
    {
        $page = $this->getPage($page);

        if (!$page) {
            return $this->templating->render('SonataPageBundle:Block:block_no_page_available.html.twig');
        }

        $container = $this->findContainer($name, $page, $parentContainer);

        if (!$container) {
            return '';
        }

        $response = $this->renderBlock($container, $page);

        if (!$response instanceof Response) {
            throw new \RunTimeException(sprintf('The container.id `%d` named `%s` from page.id `%d` must return a Response object', $container->getId(), $name, $page->getId()));
        }

        return $response->getContent();
    }

    /**
     * Return the block service linked to the link
     *
     * @param \Sonata\PageBundle\Block\BlockInterface $block
     * @return \Sonata\PageBundle\Block\BlockServiceInterface
     */
    public function getBlockService(BlockInterface $block)
    {
        if (!$this->hasBlockService($block->getType())) {
            if ($this->getDebug()) {
                throw new \RuntimeException(sprintf('The block service `%s` referenced in the block `%s` does not exists', $block->getType(), $block->getId()));
            }

            if ($this->getLogger()){
                $this->getLogger()->crit(sprintf('[cms::getBlockService] block.id=%d - service:%s does not exists', $block->getId(), $block->getType()));
            }

            return false;
        }

        return $this->blockServices[$block->getType()];
    }

    /**
     * @throws \RuntimeException
     * @param \Sonata\PageBundle\Model\BlockInterface $block
     * @return array|bool
     */
    public function getCacheService(BlockInterface $block)
    {
        if (!$this->hasCacheService($block->getType())) {
            throw new \RuntimeException(sprintf('The block service `%s` referenced in the block `%s` does not exists', $block->getType(), $block->getId()));
        }

        return $this->cacheServices[$block->getType()];
    }

    /**
     * Returns related cache services
     *
     * @return array
     */
    public function getCacheServices()
    {
        return $this->cacheServices;
    }

    /**
     *
     * @param sring $id
     * @return boolean
     */
    public function hasBlockService($id)
    {
        return isset($this->blockServices[$id]) ? true : false;
    }

    /**
     *
     * @param sring $id
     * @return boolean
     */
    public function hasCacheService($id)
    {
        return isset($this->cacheServices[$id]) ? true : false;
    }

    /**
     * return the current page
     *
     * if the current route linked to a CMS page ( route name = `page_slug`)
     *   then the page is retrieve by using a slug
     *   otherwise the page is loaded from the route name
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function defineCurrentPage($request)
    {
        $routeName = $request->get('_route');

        if ($this->currentPage) {
            return $this->currentPage;
        }

        if ($routeName == 'page_slug') { // true cms page
            return null;
        } else { // hybrid page, ie an action is used
            $this->currentPage = $this->getPageByRouteName($routeName);

            if (!$this->currentPage && $this->getLogger()) {
                $this->getLogger()->crit(sprintf('[page:getCurrentPage] no page available for route : %s', $routeName));
            }
        }

        return $this->currentPage;
    }

    public function invalidate(CacheElement $cacheElement)
    {
        $this->cacheInvalidation->invalidate($this->getCacheServices(), $cacheElement);
    }

    /**
     * Returns the current page
     *
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    public function setCurrentPage(PageInterface $page)
    {
        $this->currentPage = $page;
    }

      public function setOptions(array $options = array())
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    public function setRoutePages($route_pages)
    {
        $this->routePages = $route_pages;
    }

    public function getRoutePages()
    {
        return $this->routePages;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     *
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param array $blockServices
     * @return void
     */
    public function setBlockServices(array $blockServices)
    {
        $this->blockServices = $blockServices;
    }

    /**
     * @return array
     */
    public function getBlockServices()
    {
        return $this->blockServices;
    }

    /**
     * @return \Sonata\PageBundle\Model\PageManagerInterface
     */
    public function getPageManager()
    {
        return $this->pageManager;
    }

    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }

    public function getCacheInvalidation()
    {
        return $this->cacheInvalidation;
    }

    public function setRecorder(Recorder $recorder)
    {
        $this->recorder = $recorder;
    }

    public function getRecorder()
    {
        return $this->recorder;
    }

    /**
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @param array $params
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function renderPage(PageInterface $page, array $params = array(), Response $response = null)
    {
        if (!$response) {

            if ($page->getTarget()) {
                $page->addHeader('Location', $page->getTarget()->getUrl());
                return new Response('', 301, $page->getHeaders());
            }

            if ($page->getHeaders()) {
                $response = new Response('', 200, $page->getHeaders());
            }
        }

        $template = false;
        if ($this->getCurrentPage()) {
            $template = $this->getPageManager()->getTemplate($this->getCurrentPage()->getTemplateCode())->getPath();
        }

        if (!$template) {
            $template = $this->defaultTemplatePath;
        }

        $params  = array_merge($params, $this->getRenderPageParams($page));

        $response = $this->templating->renderResponse($template, $params, $response);
        $response->setTtl($page->getTtl());

        return $response;
    }

    protected function getRenderPageParams(PageInterface $page)
    {
        return array(
            'page'    => $page,
            'manager' => $this,
        );
    }
}
