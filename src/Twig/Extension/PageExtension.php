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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class PageExtension extends AbstractExtension
{
    private CmsManagerSelectorInterface $cmsManagerSelector;

    private SiteSelectorInterface $siteSelector;

    private RouterInterface $router;

    private BlockHelper $blockHelper;

    private RequestStack $requestStack;

    private bool $hideDisabledBlocks;

    /**
     * @param CmsManagerSelectorInterface $cmsManagerSelector A CMS manager selector
     * @param SiteSelectorInterface       $siteSelector       A site selector
     * @param RouterInterface             $router             The Router
     * @param BlockHelper                 $blockHelper        The Block Helper
     * @param bool                        $hideDisabledBlocks
     */
    public function __construct(CmsManagerSelectorInterface $cmsManagerSelector, SiteSelectorInterface $siteSelector, RouterInterface $router, BlockHelper $blockHelper, RequestStack $requestStack, $hideDisabledBlocks = false)
    {
        $this->cmsManagerSelector = $cmsManagerSelector;
        $this->siteSelector = $siteSelector;
        $this->router = $router;
        $this->blockHelper = $blockHelper;
        $this->requestStack = $requestStack;
        $this->hideDisabledBlocks = $hideDisabledBlocks;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sonata_page_ajax_url', [$this, 'ajaxUrl']),
            new TwigFunction('sonata_page_breadcrumb', [$this, 'breadcrumb'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('sonata_page_render_container', [$this, 'renderContainer'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_page_render_block', [$this, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('controller', [$this, 'controller']),
        ];
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function breadcrumb(Environment $twig, ?PageInterface $page = null, array $options = []): string
    {
        if (!$page) {
            $page = $this->cmsManagerSelector->retrieve()->getCurrentPage();
        }

        $options = array_merge([
            'separator' => '',
            'current_class' => '',
            'last_separator' => '',
            'force_view_home_page' => true,
            'container_attr' => ['class' => 'sonata-page-breadcrumbs'],
            'elements_attr' => [],
            'template' => '@SonataPage/Page/breadcrumb.html.twig',
        ], $options);

        $breadcrumbs = [];

        if ($page) {
            $breadcrumbs = $page->getParents();

            if ($options['force_view_home_page'] && (!isset($breadcrumbs[0]) || 'homepage' !== $breadcrumbs[0]->getRouteName())) {
                $site = $this->siteSelector->retrieve();

                $homePage = false;
                try {
                    if (null !== $site) {
                        $homePage = $this->cmsManagerSelector->retrieve()->getPageByRouteName($site, 'homepage');
                    }
                } catch (PageNotFoundException $e) {
                }

                if ($homePage) {
                    array_unshift($breadcrumbs, $homePage);
                }
            }
        }

        return $twig->render($options['template'], [
            'page' => $page,
            'breadcrumbs' => $breadcrumbs,
            'options' => $options,
        ]);
    }

    /**
     * Returns the URL for an ajax request for given block.
     *
     * @param PageBlockInterface   $block      Block service
     * @param array<string, mixed> $parameters Provide absolute or relative url ?
     * @param int                  $absolute
     *
     * @return string
     */
    public function ajaxUrl(PageBlockInterface $block, $parameters = [], $absolute = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $parameters['blockId'] = $block->getId();

        if ($block->getPage() instanceof PageInterface) {
            $parameters['pageId'] = $block->getPage()->getId();
        }

        return $this->router->generate('sonata_page_ajax_block', $parameters, $absolute);
    }

    /**
     * @param string                    $name
     * @param string|PageInterface|null $page
     * @param array<string, mixed>      $options
     *
     * @return string
     */
    public function renderContainer($name, $page = null, array $options = [])
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
     * @param array<string, mixed> $options
     *
     * @return string
     */
    public function renderBlock(PageBlockInterface $block, array $options = [])
    {
        if (false === $block->getEnabled() && !$this->cmsManagerSelector->isEditor() && $this->hideDisabledBlocks) {
            return '';
        }

        $page = $block->getPage();

        // defined extra default key for the cache
        $pageCacheKeys = null !== $page ? [
            'manager' => $page instanceof SnapshotPageProxy ? 'snapshot' : 'page',
            'page_id' => $page->getId(),
        ] : [];

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
     * @param string               $controller
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $query
     *
     * @return ControllerReference
     */
    public function controller($controller, $attributes = [], $query = [])
    {
        if (!isset($attributes['pathInfo'])) {
            $site = $this->siteSelector->retrieve();

            if ($site) {
                $sitePath = $site->getRelativePath();
                $request = $this->requestStack->getCurrentRequest();

                if (null !== $sitePath && null !== $request) {
                    $attributes['pathInfo'] = $sitePath.$request->getPathInfo();
                }
            }
        }

        return HttpKernelExtension::controller($controller, $attributes, $query);
    }
}
