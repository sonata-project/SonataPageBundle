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

use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\SnapshotPageProxy;

use Symfony\Component\Routing\RouterInterface;

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
     * Constructor
     *
     * @param CmsManagerSelectorInterface $cmsManagerSelector A CMS manager selector
     * @param SiteSelectorInterface       $siteSelector       A site selector
     * @param RouterInterface             $router             The Router
     */
    public function __construct(CmsManagerSelectorInterface $cmsManagerSelector, SiteSelectorInterface $siteSelector, RouterInterface $router)
    {
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->siteSelector       = $siteSelector;
        $this->router             = $router;
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
     * Returns the URL of given page
     *
     * @deprecated
     *
     * @param null|PageInterface|string $page       A Sonata page
     * @param array                     $parameters An array of parameters
     * @param boolean                   $absolute   Whether to generate an absolute URL
     *
     * @return string
     *
     * @throws \RunTimeException
     */
    public function url($page = null, array $parameters = array(), $absolute = false)
    {
        throw new \RuntimeException('The function is deprecated, please use the standard Symfony router helper');
    }

    /**
     * Returns the URL for an ajax request for given block
     *
     * @param PageBlockInterface $block    Block service
     * @param bool               $absolute Provide absolute or relative url ?
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
     * @param bool   $useCache
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderContainer($name, $page = null, $useCache = true)
    {
        $cms        = $this->cmsManagerSelector->retrieve();
        $site       = $this->siteSelector->retrieve();
        $targetPage = false;

        try {
            if ($page === null) {
                $targetPage = $cms->getCurrentPage();
            } else {
                if (!$page instanceof PageInterface && is_string($page)) {
                    $targetPage = $cms->getInternalRoute($site, $page);
                } else {
                    if ($page instanceof PageInterface) {
                        $targetPage = $page;
                    }
                }
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

        return $this->renderBlock($container, $useCache);
    }

    /**
     * @param PageBlockInterface $block
     * @param bool               $useCache
     *
     * @return string
     */
    public function renderBlock(PageBlockInterface $block, $useCache = true)
    {
        if ($block->getEnabled() === false && !$this->cmsManagerSelector->isEditor()) {
            return '';
        }

        return $this->environment->getExtension('sonata_block')->renderBlock($block, $useCache, array(
            'manager' => $block->getPage() instanceof SnapshotPageProxy ? 'snapshot' : 'page',
            'page_id' => $block->getPage()->getId(),
        ));
    }
}