<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Page;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sonata\PageBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Block\BlockServiceInterface;
use Application\Sonata\PageBundle\Entity\Page;

/**
 * The Manager class is in charge of retrieving the correct page (cms page or action page)
 *
 * An action page is linked to a symfony action and a cms page is a standalone page.
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class Manager
{
    protected $routePages = array();

    protected $currentPage;

    protected $pageLoader;

    protected $blocks = array();

    protected $options = array();

    protected $blockServices = array();

    protected $logger;

    protected $blockManager;

    protected $pageManager;

    protected $templating;

    protected $debug = false;

    public function __construct(PageManagerInterface $pageManager, BlockManagerInterface $blockManager, EngineInterface $templating)
    {
        $this->pageManager  = $pageManager;
        $this->blockManager = $blockManager;
        $this->templating   = $templating;
    }

    /**
     * filter the `core.response` event to decorated the action
     *
     * @param  $event
     * @param  $response
     * @return
     */
    public function onCoreResponse($event)
    {

        $response    = $event->getResponse();
        $requestType = $event->getRequestType();
        $request     = $event->getRequest();

        if($this->isDecorable($request, $requestType, $response)) {

            $page = $this->defineCurrentPage($request);

            if ($page && $page->getDecorate()) {
                $template = 'SonataPageBundle::layout.html.twig';
                if($this->getCurrentPage()) {
                    $template = $this->getCurrentPage()->getTemplate()->getPath();
                }

                $response->setContent(
                    $this->templating->render(
                        $template,
                        array(
                            'content'   => $response->getContent(),
                            'page'      => $page
                        )
                    )
                );
            }
        }
        
        return $response;
    }

    public function addBlockService($name, BlockServiceInterface $service)
    {
        $this->blockServices[$name] = $service;
    }
    
    /**
     * return true is the page can be decorate with an outter template
     *
     * @param  $request_type
     * @param Response $response
     * @return bool
     */
    public function isDecorable(Request $request, $requestType, Response $response)
    {

        if($requestType != HttpKernelInterface::MASTER_REQUEST) {

            return false;
        }

        if(($response->headers->get('Content-Type') ?: 'text/html') != 'text/html') {

            return false;
        }

        if($response->getStatusCode() != 200) {

            return false;
        }
        
        if($request->headers->get('x-requested-with') == 'XMLHttpRequest') {
            return false;
        }

        $routeName = $request->get('_route');
        if(!$routeName) {
            return false;
        }

        foreach($this->getOption('ignore_routes', array()) as $route) {

            if($routeName == $route) {
                return false;
            }
        }

        foreach($this->getOption('ignore_route_patterns', array()) as $routePattern) {
            if(preg_match($routePattern, $routeName)) {
                return false;
            }
        }

        $uri = $request->getRequestUri();
        foreach($this->getOption('ignore_uri_patterns', array()) as $uri_pattern) {
            if(preg_match($uri_pattern, $uri)) {

                return false;
            }
        }

        return true;

    }

    /**
     * render a specialize block
     *
     * @param  $block
     * @return string | Response
     */
    public function renderBlock(BlockInterface $block, PageInterface $page)
    {

        if($this->getLogger()) {
            $this->getLogger()->crit(sprintf('[cms::renderBlock] block.id=%d, block.type=%s ', $block->getId(), $block->getType()));
        }

        
        try {
            $service = $this->getBlockService($block);

            if(!$service) {
                
                return '';
            }
            return $service->execute($block, $page);
        } catch (\Exception $e) {
            if($this->getDebug()) {

                throw $e;
            }
            
            if($this->getLogger()) {
                $this->getLogger()->crit(sprintf('[cms::renderBlock] block.id=%d - error while rendering block - %s', $block->getId(), $e->getMessage()));
            }

        }

        return '';
    }

    public function findContainer($name, PageInterface $page, BlockInterface $parentContainer = null)
    {

        $container = false;
        
        if($parentContainer) {
            // parent container is set, nothing to find, don't need to loop across the
            // name to find the correct container (main template level)
            $container = $parentContainer;
        }

        // first level blocks are containers
        if(!$container && $page->getBlocks()) {
            foreach($page->getBlocks() as $block) {
                if($block->getSetting('name') == $name) {

                    $container = $block;
                    break;
                }
            }
        }

        if(!$container) {
          
            $container = $this->blockManager->createNewContainer(array(
                'enabled' => true,
                'page' => $page,
                'name' => $name,
                'position' => 1
            ));

            if($parentContainer) {
                $container->setParent($parentContainer);
            }

            $this->blockManager->save($container);
        }

        return $container;
    }

    
    /**
     *
     * return the block service linked to the link
     * 
     * @param Sonata\PageBundle\Block\BlockInterface $block
     * @return Sonata\PageBundle\Block\BlockServiceInterface
     */
    public function getBlockService(BlockInterface $block)
    {

        if(!$this->hasBlockService($block->getType())) {
            if($this->getDebug()) {
                throw new \RuntimeException(sprintf('The block service `%s` referenced in the block `%s` does not exists', $block->getType(), $block->getId()));
            }

            if($this->getLogger()){
                $this->getLogger()->crit(sprintf('[cms::getBlockService] block.id=%d - service:%s does not exists', $block->getId(), $block->getType()));
            }

            return false;
        }

        return $this->blockServices[$block->getType()];
    }

    /**
     *
     * @return boolean
     */
    public function hasBlockService($id)
    {
        return isset($this->blockServices[$id]) ? true : false;
    }

    /**
     * return a fully loaded page ( + blocks ) from a route name
     *
     * if the page does not exists then the page is created.
     *
     * @param  $routeName
     * @return Application\Sonata\PageBundle\Entity\Page|bool
     */
    public function getPageByRouteName($routeName)
    {

        if(!isset($this->routePages[$routeName])) {
            $page = $this->pageManager->getPageByName($routeName);

            if(!$page) {

                if(!$this->getDefaultTemplate()) {
                    throw new \RuntimeException('No default template defined');
                }

                $page = $this->pageManager->createNewPage(array(
                    'template' => $this->getDefaultTemplate(),
                    'enabled'  => true,
                    'routeName' => $routeName,
                    'name'      => $routeName,
                    'loginRequired' => false,
                ));

                $this->pageManager->save($page);

            }

            $this->loadBlocks($page);
            $this->routePages[$routeName] = $page;
        }

        return $this->routePages[$routeName];
    }

    /**
     * return the default template used in the current application
     *
     * @return bool | Application\Sonata\PageBundle\Entity\Template
     */
    public function getDefaultTemplate()
    {
        return $this->pageManager->getDefaultTemplate();
    }

    /**
     * return a fully loaded CMS page ( + blocks ) 
     *
     * @param string $slug
     * @return bool
     */
    public function getPageBySlug($slug)
    {

        $page = $this->pageManager->getPageBySlug($slug);

        if($page) {
            $this->loadBlocks($page);
        }

        return $page;
    }

    /**
     * return the current page
     *
     * if the current route linked to a CMS page ( route name = `page_slug`)
     *   then the page is retrieve by using a slug
     *   otherwise the page is loaded from the route name
     *
     * @return Application\Sonata\PageBundle\Entity\Page
     */
    public function defineCurrentPage($request)
    {

        $routeName = $request->get('_route');

        if($routeName == 'page_slug') { // true cms page
            $slug = $request->get('slug');

            $this->currentPage = $this->getPageBySlug($slug);

            if(!$this->currentPage && $this->getLogger()) {

                $this->getLogger()->crit(sprintf('[page:getCurrentPage] no page available for slug : %s', $slug));
            }

        } else { // hybrid page, ie an action is used
            $this->currentPage = $this->getPageByRouteName($routeName);

            if(!$this->currentPage && $this->getLogger()) {
                $this->getLogger()->crit(sprintf('[page:getCurrentPage] no page available for route : %s', $routeName));
            }
        }

        return $this->currentPage;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     *
     * @param string $id
     */
    public function getBlock($id)
    {
        if(!isset($this->blocks[$id])) {

            $this->blocks[$id] = $this->blockManager->getBlock($id);
        }

        return $this->blocks[$id];
    }

    /**
     * load all the related nested blocks linked to one page.
     *
     * @param PageInterface $page
     * @return void
     */
    public function loadBlocks(PageInterface $page)
    {

        $blocks = $this->blockManager->loadPageBlocks($page);

        // save a local cache
        foreach($blocks as $block) {
            $this->blocks[$block->getId()] = $block;
        }
    }

    /**
     * save the block order from the page disposition
     *
     * Format :
     *      Array
     *      (
     *          [cms-block-2] => Array
     *              (
     *                  [type] => core.container
     *                  [child] => Array
     *                      (
     *                          [cms-block-4] => Array
     *                              (
     *                                  [type] => core.action
     *                                  [child] =>
     *                              )
     *
     *                      )
     *
     *              )
     *
     *          [cms-block-5] => Array
     *              (
     *                  [type] => core.container
     *                  [child] =>
     *              )
     *
     *          [cms-block-8] => Array
     *              (
     *                  [type] => core.container
     *                  [child] => Array
     *                      (
     *                          [cms-block-9] => Array
     *                              (
     *                                  [type] => core.container
     *                                  [child] => Array
     *                                      (
     *                                          [cms-block-3] => Array
     *                                              (
     *                                                  [type] => core.text
     *                                                  [child] =>
     *                                              )
     *
     *                                      )
     *
     *                              )
     *
     *                      )
     *
     *              )
     *
     *      )
     *
     * @param  $data
     * @return void
     */
    public function savePosition($data)
    {
        return $this->blockManager->saveBlocksPosition($data);
    }

    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
    }

    public function getBlocks()
    {
        return $this->blocks;
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

    public function getDebug()
    {
        return $this->debug;
    }
}