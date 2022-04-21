<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Twig\Extension;

use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * PageExtension.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class PageExtension extends AbstractExtension
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
     * @var RouterInterface
     */
    private $router;

    /**
     * @var BlockHelper
     */
    private $blockHelper;

    /**
     * @var HttpKernelExtension
     */
    private $httpKernelExtension;

    /**
     * @var bool
     */
    private $hideDisabledBlocks;

    /**
     * @param CmsManagerSelectorInterface $cmsManagerSelector A CMS manager selector
     * @param SiteSelectorInterface       $siteSelector       A site selector
     * @param RouterInterface             $router             The Router
     * @param BlockHelper                 $blockHelper        The Block Helper
     * @param bool                        $hideDisabledBlocks
     */
    public function __construct(CmsManagerSelectorInterface $cmsManagerSelector, SiteSelectorInterface $siteSelector, RouterInterface $router, BlockHelper $blockHelper, HttpKernelExtension $httpKernelExtension, $hideDisabledBlocks = false)
    {
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->siteSelector = $siteSelector;
        $this->router = $router;
        $this->blockHelper = $blockHelper;
        $this->httpKernelExtension = $httpKernelExtension;
        $this->hideDisabledBlocks = $hideDisabledBlocks;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sonata_page_ajax_url', [$this, 'ajaxUrl']),
            new TwigFunction('sonata_page_render_container', [$this, 'renderContainer'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_page_render_block', [$this, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('controller', [$this, 'controller']),
        ];
    }

    /**
     * Returns the URL for an ajax request for given block.
     *
     * @param PageBlockInterface $block      Block service
     * @param array              $parameters Provide absolute or relative url ?
     * @param int                $absolute
     *
     * @return string
     */
    public function ajaxUrl(PageBlockInterface $block, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $parameters['blockId'] = $block->getId();

        if ($block->getPage() instanceof PageInterface) {
            $parameters['pageId'] = $block->getPage()->getId();
        }

        return $this->router->generate('sonata_page_ajax_block', $parameters, $absolute);
    }

    /**
     * @param string $name
     * @param null   $page
     *
     * @return string
     */
    public function renderContainer($name, $page = null, array $options = []): string
    {
        $cms = $this->cmsManagerSelector->retrieve();
        $site = $this->siteSelector->retrieve();
        $targetPage = false;

        try {
            if (null === $page) {
                $targetPage = $cms->getCurrentPage();
            } elseif (null !== $site && !$page instanceof PageInterface && \is_string($page)) {
                $targetPage = $cms->getInternalRoute($site, $page);
            } elseif ($page instanceof PageInterface) {
                $targetPage = $page;
            }
        } catch (PageNotFoundException $e) {
            // the snapshot does not exist
            $targetPage = false;
        }

        if (!$targetPage) {
            return '';
        }

        $container = $cms->findContainer($name, $targetPage);

        if (!$container) {
            return '';
        }

        return $this->renderBlock($container, $options);
    }

    /**
     * @param array $options
     * @return string
     */
    public function renderBlock(PageBlockInterface $block, array $options = []): string
    {
        if (false === $block->getEnabled() && !$this->cmsManagerSelector->isEditor() && $this->hideDisabledBlocks) {
            return '';
        }

        // defined extra default key for the cache
        $pageCacheKeys = [
            'manager' => $block->getPage() instanceof SnapshotPageProxy ? 'snapshot' : 'page',
            'page_id' => $block->getPage()->getId(),
        ];

        // build the parameters array
        $options = array_merge([
            'use_cache' => $options['use_cache'] ?? true,
            'extra_cache_keys' => [],
        ], $pageCacheKeys, $options);

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
     * @return ControllerReference
     */
    public function controller(Environment $environment, $controller, $attributes = [], $query = [])
    {
        if (!isset($attributes['pathInfo'])) {
            $site = $this->siteSelector->retrieve();
            if ($site) {
                $sitePath = $site->getRelativePath();
                $globals = $environment->getGlobals();
                $currentPathInfo = $globals['app']->getRequest()->getPathInfo();

                $attributes['pathInfo'] = $sitePath.$currentPathInfo;
            }
        }

        return HttpKernelExtension::controller($controller, $attributes, $query);
    }

    public function getName(): string
    {
        return 'sonata_page';
    }
}
