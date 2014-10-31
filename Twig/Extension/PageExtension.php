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

use Sonata\PageBundle\Cache\HttpCacheHandlerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\SnapshotPageProxy;

use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Component\Routing\RouterInterface;
use Sonata\BlockBundle\Templating\Helper\BlockHelper;

/**
 * PageExtension
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PageExtension extends \Twig_Extension
{
    /**
     * @var CmsManagerSelectorInterface
     */
    private $cmsManagerSelector;

    /**
     * @var SiteSelectorInterface
     */
    private $siteSelector;

    /**
     * @var array
     */
    private $resources;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var BlockHelper
     */
    private $blockHelper;

    /**
     * @var \Symfony\Bridge\Twig\Extension\HttpKernelExtension
     */
    private $httpKernelExtension;

    /**
     * Constructor
     *
     * @param CmsManagerSelectorInterface $cmsManagerSelector  A CMS manager selector
     * @param SiteSelectorInterface       $siteSelector        A site selector
     * @param RouterInterface             $router              The Router
     * @param BlockHelper                 $blockHelper         The Block Helper
     * @param HttpKernelExtension         $httpKernelExtension
     */
    public function __construct(CmsManagerSelectorInterface $cmsManagerSelector, SiteSelectorInterface $siteSelector, RouterInterface $router, BlockHelper $blockHelper, HttpKernelExtension $httpKernelExtension)
    {
        $this->cmsManagerSelector  = $cmsManagerSelector;
        $this->siteSelector        = $siteSelector;
        $this->router              = $router;
        $this->blockHelper         = $blockHelper;
        $this->httpKernelExtension = $httpKernelExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'sonata_page_ajax_url'            => new \Twig_Function_Method($this, 'ajaxUrl'),
            'sonata_page_url'                 => new \Twig_Function_Method($this, 'url'),
            'sonata_page_breadcrumb'          => new \Twig_Function_Method($this, 'breadcrumb', array('is_safe' => array('html'))),
            'sonata_page_render_container'    => new \Twig_Function_Method($this, 'renderContainer', array('is_safe' => array('html'))),
            'sonata_page_render_block'        => new \Twig_Function_Method($this, 'renderBlock', array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('controller', array($this, 'controller'))
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_page';
    }

    /**
     * @param PageInterface $page
     * @param array         $options
     *
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
            'container_attr'       => array('class' => 'sonata-page-breadcrumbs'),
            'elements_attr'        => array(),
            'template'             => 'SonataPageBundle:Page:breadcrumb.html.twig',
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

        return $this->render($options['template'], array(
            'page'        => $page,
            'breadcrumbs' => $breadcrumbs,
            'options'     => $options
        ));
    }

    /**
     * Returns the URL for an ajax request for given block
     *
     * @param PageBlockInterface $block      Block service
     * @param array              $parameters Provide absolute or relative url ?
     * @param boolean            $absolute
     *
     * @return string
     */
    public function ajaxUrl(PageBlockInterface $block, $parameters = array(), $absolute = false)
    {
        $parameters['blockId'] = $block->getId();

        if ($block->getPage() instanceof PageInterface) {
            $parameters['pageId']  = $block->getPage()->getId();
        }

        return $this->router->generate('sonata_page_ajax_block', $parameters, $absolute);
    }

    /**
     * @param string $template
     * @param array  $parameters
     *
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
     * @param string $name
     * @param null   $page
     * @param array  $options
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderContainer($name, $page = null, array $options = array())
    {
        $cms        = $this->cmsManagerSelector->retrieve();
        $site       = $this->siteSelector->retrieve();
        $targetPage = false;

        try {
            if ($page === null) {
                $targetPage = $cms->getCurrentPage();
            } else if (!$page instanceof PageInterface && is_string($page)) {
                $targetPage = $cms->getInternalRoute($site, $page);
            } else if ($page instanceof PageInterface) {
                $targetPage = $page;
            }
        } catch (PageNotFoundException $e) {
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

        return $this->renderBlock($container, $options);
    }

    /**
     * @param PageBlockInterface $block
     * @param array              $options
     *
     * @return string
     */
    public function renderBlock(PageBlockInterface $block, array $options = array())
    {
        if ($block->getEnabled() === false && !$this->cmsManagerSelector->isEditor()) {
            return '';
        }

        // defined extra default key for the cache
        $pageCacheKeys = array(
            'manager'   => $block->getPage() instanceof SnapshotPageProxy ? 'snapshot' : 'page',
            'page_id'   => $block->getPage()->getId(),
        );

        // build the parameters array
        $options = array_merge(array(
            'use_cache'        => isset($options['use_cache']) ? $options['use_cache'] : true,
            'extra_cache_keys' => array()
        ), $pageCacheKeys, $options);

        // make sure the parameters array contains all valid keys
        $options['extra_cache_keys'] = array_merge($options['extra_cache_keys'], $pageCacheKeys);

        return $this->blockHelper->render($block, $options);
    }

    /**
     * Forwards pathInfo to subrequests.
     * Allows HostPathSiteSelector to work.
     *
     * @param string $controller
     * @param array  $attributes
     * @param array  $query
     *
     * @return \Symfony\Component\HttpKernel\Controller\ControllerReference
     */
    public function controller($controller, $attributes = array(), $query = array())
    {
        $globals = $this->environment->getGlobals();

        if (!isset($attributes['pathInfo'])) {
            $sitePath = $this->siteSelector->retrieve()->getRelativePath();
            $currentPathInfo = $globals['app']->getRequest()->getPathInfo();

            $attributes['pathInfo'] = $sitePath . $currentPathInfo;
        }

        return $this->httpKernelExtension->controller($controller, $attributes, $query);
    }
}
