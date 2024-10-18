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
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Twig\PageRuntime;
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
    private PageRuntime $pageRuntime;

    /**
     * NEXT_MAJOR: Remove this constructor.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(
        CmsManagerSelectorInterface $cmsManagerSelector,
        SiteSelectorInterface $siteSelector,
        RouterInterface $router,
        BlockHelper $blockHelper,
        RequestStack $requestStack,
        bool $hideDisabledBlocks = false,
    ) {
        $this->pageRuntime = new PageRuntime(
            $cmsManagerSelector,
            $siteSelector,
            $router,
            $blockHelper,
            $requestStack,
            null,
            $hideDisabledBlocks,
        );
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sonata_page_ajax_url', [PageRuntime::class, 'ajaxUrl']),
            new TwigFunction('sonata_page_breadcrumb', [PageRuntime::class, 'breadcrumb'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('sonata_page_render_container', [PageRuntime::class, 'renderContainer'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_page_render_block', [PageRuntime::class, 'renderBlock'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_page_url', [PageRuntime::class, 'getPageUrl']),
            new TwigFunction('sonata_page_path', [PageRuntime::class, 'getPagePath']),
            new TwigFunction('controller', [PageRuntime::class, 'controller']),
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
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            PageRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->pageRuntime->breadcrumb($twig, $page, $options);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function ajaxUrl(PageBlockInterface $block, array $parameters = [], int $absolute = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            PageRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->pageRuntime->ajaxUrl($block, $parameters, $absolute);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function renderContainer(string $name, string|PageInterface|null $page = null, array $options = []): string
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            PageRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->pageRuntime->renderContainer($name, $page, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function renderBlock(PageBlockInterface $block, array $options = []): string
    {
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            PageRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->pageRuntime->renderBlock($block, $options);
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
        @trigger_error(\sprintf(
            'The method "%s()" is deprecated since sonata-project/page-bundle 4.7 and will be removed in 5.0.'
            .'  Use "%s::%s()" instead.',
            __METHOD__,
            PageRuntime::class,
            __FUNCTION__
        ), \E_USER_DEPRECATED);

        return $this->pageRuntime->controller($controller, $attributes, $query);
    }
}
