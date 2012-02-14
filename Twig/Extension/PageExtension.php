<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Twig\Extension;

use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;

use Sonata\CacheBundle\Cache\CacheManagerInterface;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Util\RecursiveBlockIteratorIterator;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;

class PageExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    /**
     * @var \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface
     */
    private $cmsManagerSelector;

    private $blockManager;

    private $cacheManager;

    /**
     * @var \Sonata\PageBundle\Site\SiteSelectorInterface
     */
    private $siteSelector;

    /**
     * @var array
     */
    private $resources;

    private $environment;

    private $cacheBlocks;

    /**
     * @param \Symfony\Component\Routing\Router $router
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsManagerSelector
     * @param \Sonata\PageBundle\Site\SiteSelectorInterface $siteSelector
     * @param \Sonata\BlockBundle\Block\BlockServiceManagerInterface $blockManager
     * @param \Sonata\CacheBundle\Cache\CacheManagerInterface $cacheManager
     * @param array $cacheBlocks
     */
    public function __construct(Router $router, CmsManagerSelectorInterface $cmsManagerSelector, SiteSelectorInterface $siteSelector, BlockServiceManagerInterface $blockManager, CacheManagerInterface $cacheManager, array $cacheBlocks)
    {
        $this->router              = $router;
        $this->cmsManagerSelector  = $cmsManagerSelector;
        $this->siteSelector        = $siteSelector;
        $this->blockManager        = $blockManager;
        $this->cacheManager        = $cacheManager;
        $this->cacheBlocks         = $cacheBlocks;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'sonata_page_url'                 => new \Twig_Function_Method($this, 'url'),
            'sonata_page_breadcrumb'          => new \Twig_Function_Method($this, 'breadcrumb', array('is_safe' => array('html'))),
            'sonata_page_render_container'    => new \Twig_Function_Method($this, 'renderContainer', array('is_safe' => array('html'))),
            'sonata_page_render_block'        => new \Twig_Function_Method($this, 'renderBlock', array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sonata_page';
    }

    /**
     * @param null|\Sonata\PageBundle\Model\PageInterface $page
     * @param array $options
     * @return string
     */
    public function breadcrumb(PageInterface $page = null, array $options = array())
    {
        if (!$page) {
            $page = $this->cmsManagerSelector->retrieve()->getCurrentPage();
        }

        $options = array_merge(array(
            'separator'            => '',
            'current_class'        => '',
            'last_separator'       => '',
            'force_view_home_page' => true,
            'container_attr'       => array('class' => 'sonata-page-breadcrumbs')
        ), $options);

        $breadcrumbs = array();

        if ($page) {
            $breadcrumbs = $page->getParents();

            if ($options['force_view_home_page'] && (!isset($breadcrumbs[0]) || $breadcrumbs[0]->getRouteName() != 'homepage')) {

                try {
                    $homePage = $this->cmsManagerSelector->retrieve()->getPageByRouteName($this->siteSelector->retrieve(), 'homepage');
                } catch (PageNotFoundException $e) {
                    $homePage = false;
                }

                if ($homePage) {
                    array_unshift($breadcrumbs, $homePage);
                }
            }
        }

        return $this->render('SonataPageBundle:Page:breadcrumb.html.twig', array(
            'page'        => $page,
            'breadcrumbs' => $breadcrumbs,
            'options'     => $options
        ));
    }

    /**
     * @throws \RunTimeException
     * @param null|\Sonata\PageBundle\Model\PageInterface|string $page
     * @param bool $absolute
     * @return string
     */
    public function url($page = null, $absolute = false)
    {
        if (!$page) {
             return '';
        }

        $context = $this->router->getContext();

        if ($page instanceof PageInterface) {
            if ($page->isDynamic()) {
                if ($this->environment->isDebug()) {
                    throw new \RunTimeException('Unable to generate path for dynamic page');
                }

                return '';
            }

            $url = $page->getCustomUrl() ?: $page->getUrl();
        } else {
            $url = $page;
        }

        $url = sprintf('%s%s', $context->getBaseUrl(), $url);

        if ($absolute && $context->getHost()) {
            $scheme = $context->getScheme();

            $port = '';
            if ('http' === $scheme && 80 != $context->getHttpPort()) {
                $port = ':'.$context->getHttpPort();
            } elseif ('https' === $scheme && 443 != $context->getHttpsPort()) {
                $port = ':'.$context->getHttpsPort();
            }

            $url = $scheme.'://'.$context->getHost().$port.$url;
        }

        return $url;
    }

    /**
     * @param string $template
     * @param array $parameters
     * @return string
     */
    private function render($template, array $parameters = array())
    {
        if (!isset($this->resources[$template])) {
            $this->resources[$template] = $this->environment->loadTemplate($template);
        }

        return $this->resources[$template]->render($parameters);
    }

    /**
     * @param $name
     * @param null $page
     * @param bool $useCache
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderContainer($name, $page = null, $useCache = true)
    {
        $cms  = $this->cmsManagerSelector->retrieve();
        $site = $this->siteSelector->retrieve();
        $targetPage = false;

        try {
            if ($page === null) {
                $targetPage = $cms->getCurrentPage();
            } else if (!$page instanceof PageInterface) {
                $targetPage = $cms->getPage($site, $page);
            } else if ($page instanceof PageInterface) {
                $targetPage = $page;
            }
        } catch(PageNotFoundException $e) {
            // the snapshot does not exist
            $targetPage = false;
        }

        if (!$targetPage) {
            return "";
        }

        $container = $cms->findContainer($name, $targetPage);

        if (!$container) {
            return "";
        }

        return $this->renderBlock($container, $useCache);
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @param bool $useCache
     * @return string
     */
    public function renderBlock(BlockInterface $block, $useCache = true)
    {
        $cacheService = $cacheKeys = false;

        if ($useCache && ($cacheService = $this->getCacheService($block))) {
            $cacheKeys = $this->blockManager->getBlockService($block)->getCacheKeys($block);

            if ($cacheService->has($cacheKeys)) {
                $cacheElement = $cacheService->get($cacheKeys);

                if (!$cacheElement->isExpired()) {
                    return $cacheElement->getData();
                }
            }
        }

        $recorder = $this->cacheManager->getRecorder();

        if ($recorder) {
            $recorder->push();
        }

        $response = $this->blockManager->renderBlock($block);

        $contextualKeys = $recorder ? $recorder->pop() : array();

        if ($response->isCacheable() && $useCache && $cacheKeys && $cacheService) {
            $cacheService->set($cacheKeys, $response, $block->getTtl(), $contextualKeys);
        }

        return $response->getContent();
    }

    /**
     * @param \Sonata\BlockBundle\Model\BlockInterface $block
     * @return \Sonata\CacheBundle\Cache\CacheInterface;
     */
    protected function getCacheService(BlockInterface $block)
    {
        $type = isset($this->cacheBlocks[$block->getType()]) ? $this->cacheBlocks[$block->getType()] : false;

        if (!$type) {
            return false;
        }

        return $this->cacheManager->getCacheService($type);
    }
}