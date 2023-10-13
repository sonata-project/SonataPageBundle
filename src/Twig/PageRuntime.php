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

namespace Sonata\PageBundle\Twig;

use Sonata\BlockBundle\Templating\Helper\BlockHelper;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Route\CmsPageRouter;
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
use Twig\Extension\RuntimeExtensionInterface;

class PageRuntime implements RuntimeExtensionInterface
{
    /**
     * @internal This class should only be used through Twig
     */
    public function __construct(
        private CmsManagerSelectorInterface $cmsManagerSelector,
        private SiteSelectorInterface $siteSelector,
        private RouterInterface $router,
        private BlockHelper $blockHelper,
        private RequestStack $requestStack,
        // NEXT_MAJOR: Stop supporting null for CmsPageRouter
        private ?CmsPageRouter $cmsPageRouter = null,
        private bool $hideDisabledBlocks = false
    ) {
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
     * @param array<mixed> $parameters
     */
    public function getPagePath(string|PageInterface $name, array $parameters = [], bool $relative = false): string
    {
        if (null === $this->cmsPageRouter) {
            throw new \RuntimeException('Cannot generate a page path without a cms page router.');
        }

        return $this->cmsPageRouter->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * @param array<mixed> $parameters
     */
    public function getPageUrl(string|PageInterface $name, array $parameters = [], bool $schemeRelative = false): string
    {
        if (null === $this->cmsPageRouter) {
            throw new \RuntimeException('Cannot generate a page path without a cms page router.');
        }

        return $this->cmsPageRouter->generate($name, $parameters, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
