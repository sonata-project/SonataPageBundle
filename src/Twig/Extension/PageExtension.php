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
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Bridge\Twig\Extension\HttpKernelExtension;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
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
    public function __construct(
        private CmsManagerSelectorInterface $cmsManagerSelector,
        private SiteSelectorInterface $siteSelector,
        private RouterInterface $router,
        private BlockHelper $blockHelper,
        private RequestStack $requestStack,
        private bool $hideDisabledBlocks = false
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sonata_page_ajax_url', [$this, 'ajaxUrl']),
            new TwigFunction('sonata_page_breadcrumb', [$this, 'breadcrumb'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('sonata_page_render_container', [$this, 'renderContainer'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_page_render_block', [$this, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('controller', [$this, 'controller']),
            new TwigFunction('sonata_page_url', [$this, 'pageUrl']),
            new TwigFunction('sonata_page_path', [$this, 'pagePath']),
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
        if (null === $page) {
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

        if (null !== $page) {
            $breadcrumbs = $page->getParents();

            if (true === $options['force_view_home_page'] && (!isset($breadcrumbs[0]) || 'homepage' !== $breadcrumbs[0]->getRouteName())) {
                $site = $this->siteSelector->retrieve();

                $homePage = null;
                try {
                    if (null !== $site) {
                        $homePage = $this->cmsManagerSelector->retrieve()->getPageByRouteName($site, 'homepage');
                    }
                } catch (PageNotFoundException) {
                }

                if (null !== $homePage) {
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
     * @param array<string, mixed> $parameters
     */
    public function ajaxUrl(PageBlockInterface $block, array $parameters = [], int $absolute = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $parameters['blockId'] = $block->getId();
        $page = $block->getPage();

        if (null !== $page) {
            $parameters['pageId'] = $page->getId();
        }

        return $this->router->generate('sonata_page_ajax_block', $parameters, $absolute);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function renderContainer(string $name, string|PageInterface|null $page = null, array $options = []): string
    {
        $cms = $this->cmsManagerSelector->retrieve();
        $site = $this->siteSelector->retrieve();
        $targetPage = null;

        try {
            if (null === $page) {
                $targetPage = $cms->getCurrentPage();
            } elseif (null !== $site && !$page instanceof PageInterface) {
                $targetPage = $cms->getInternalRoute($site, $page);
            } elseif ($page instanceof PageInterface) {
                $targetPage = $page;
            }
        } catch (PageNotFoundException) {
            // the snapshot does not exist
            $targetPage = null;
        }

        if (null === $targetPage) {
            return '';
        }

        $container = $cms->findContainer($name, $targetPage);

        if (null === $container) {
            return '';
        }

        return $this->renderBlock($container, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function renderBlock(PageBlockInterface $block, array $options = []): string
    {
        if (
            false === $block->getEnabled()
            && !$this->cmsManagerSelector->isEditor()
            && $this->hideDisabledBlocks
        ) {
            return '';
        }

        return $this->blockHelper->render($block, $options);
    }

    /**
     * Forwards pathInfo to subrequests.
     * Allows HostPathSiteSelector to work.
     *
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $query
     */
    public function controller(string $controller, array $attributes = [], array $query = []): ControllerReference
    {
        if (!isset($attributes['pathInfo'])) {
            $site = $this->siteSelector->retrieve();

            if (null !== $site) {
                $sitePath = $site->getRelativePath();
                $request = $this->requestStack->getCurrentRequest();

                if (null !== $sitePath && null !== $request) {
                    $attributes['pathInfo'] = $sitePath.$request->getPathInfo();
                }
            }
        }

        return HttpKernelExtension::controller($controller, $attributes, $query);
    }
    
    /**
     * @param PageInterface $page
     * @param array $parameters
     * @return string
     */
    public function pageUrl(PageInterface $page, array $parameters = []) : string
    {
        $parameters = array_merge($parameters, [
            RouteObjectInterface::ROUTE_OBJECT => $page
        ]);
        return $this->router->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, $parameters, RouterInterface::ABSOLUTE_URL);
    }

    /**
     * @param PageInterface $page
     * @param array $parameters
     * @return string
     */
    public function pagePath(PageInterface $page, array $parameters = []) : string
    {
        $parameters = array_merge($parameters, [
            RouteObjectInterface::ROUTE_OBJECT => $page
        ]);
        return $this->router->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, $parameters, RouterInterface::ABSOLUTE_PATH);
    }    
}
