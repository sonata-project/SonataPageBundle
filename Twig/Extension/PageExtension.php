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

    /**
     * @var \Sonata\PageBundle\Site\SiteSelectorInterface
     */
    private $siteSelector;

    /**
     * @var array
     */
    private $resources;

    private $environment;

    /**
     * @param \Symfony\Component\Routing\Router $router
     * @param \Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface $cmsManagerSelector
     * @param \Sonata\PageBundle\Site\SiteSelectorInterface $siteSelector
     */
    public function __construct(Router $router, CmsManagerSelectorInterface $cmsManagerSelector, SiteSelectorInterface $siteSelector)
    {
        $this->router             = $router;
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->siteSelector       = $siteSelector;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'page_url'                 => new \Twig_Function_Method($this, 'url'),
            'page_breadcrumb'          => new \Twig_Function_Method($this, 'breadcrumb', array('is_safe' => array('html'))),
            'page_include_stylesheets' => new \Twig_Function_Method($this, 'includeStylesheets', array('is_safe' => array('html'))),
            'page_include_javascripts' => new \Twig_Function_Method($this, 'includeJavascripts', array('is_safe' => array('html'))),
            'page_render_container'    => new \Twig_Function_Method($this, 'renderContainer', array('is_safe' => array('html'))),
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
     * @return
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
     * @param $media
     * @param null|\Sonata\PageBundle\Model\PageInterface $page
     * @return array|string
     */
    public function includeJavascripts($media, PageInterface $page = null)
    {
        $cms = $this->cmsManagerSelector->retrieve();

        $services = $cms->getBlockServices();

        $javascripts = array();

        foreach ($this->getServicesType($page) as $id) {
            $service = isset($services[$id]) ? $services[$id] : false;

            if (!$service) {
                continue;
            }

            $javascripts = array_merge($javascripts, $service->getJavacripts($media));
        }

        if (count($javascripts) == 0) {
            return '';
        }

        $html = "";
        foreach ($javascripts as $javascript) {
            $html .= "\n" . sprintf('<script src="%s" type="text/javascript"></script>', $javascript);
        }

        return $html;
    }

    /**
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return array
     */
    private function getServicesType(PageInterface $page = null)
    {
        $services = array();

        if ($page) {
            $blocks = new RecursiveBlockIteratorIterator($page->getBlocks());
        } else {
            $blocks = $this->cmsManagerSelector->retrieve()->getBlocks();
        }

        foreach ($blocks as $block) {
            $services[] = $block->getType();
        }

        return array_unique($services);
    }

    /**
     * @param $media
     * @param null|\Sonata\PageBundle\Model\PageInterface $page
     * @return array|string
     */
    public function includeStylesheets($media, PageInterface $page = null)
    {
        $cms = $this->cmsManagerSelector->retrieve();

        $services = $cms->getBlockServices();

        $stylesheets = array();

        foreach ($this->getServicesType($page) as $id) {
            $service = isset($services[$id]) ? $services[$id] : false;

            if (!$service) {
                continue;
            }

            $stylesheets = array_merge($stylesheets, $service->getStylesheets($media));
        }

        if (count($stylesheets) == 0) {
            return '';
        }

        $html = sprintf("<style type='text/css' media='%s'>", $media);

        foreach ($stylesheets as $stylesheet) {
            $html .= "\n" . sprintf('@import url(%s);', $stylesheet, $media);
        }

        $html .= "\n</style>";

        return $html;
    }

    /**
     * @param $name
     * @param $page
     * @return string
     */
    public function renderContainer($name, $page = null)
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

        return $cms->renderContainer($site, $name, $targetPage);
    }
}

