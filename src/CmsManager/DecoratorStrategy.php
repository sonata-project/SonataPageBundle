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

namespace Sonata\PageBundle\CmsManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class DecoratorStrategy implements DecoratorStrategyInterface
{
    /**
     * @param array<string> $ignoreRoutes
     * @param array<string> $ignoreRoutePatterns
     * @param array<string> $ignoreUriPatterns
     */
    public function __construct(
        private array $ignoreRoutes,
        private array $ignoreRoutePatterns,
        private array $ignoreUriPatterns
    ) {
    }

    public function isDecorable(Request $request, int $requestType, Response $response): bool
    {
        // TODO: Simplify this when dropping support for Symfony <  5.3
        $mainRequestType = \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : 1;

        if ($mainRequestType !== $requestType) {
            return false;
        }

        if (!str_starts_with($response->headers->get('Content-Type') ?? 'text/html', 'text/html')) {
            return false;
        }

        if ('1' === $response->headers->get('x-sonata-page-not-decorable', '0')) {
            return false;
        }

        // the main controller explicitly force the the page to be decorate
        if ('1' === $response->headers->get('x-sonata-page-decorable', '0')) {
            return true;
        }

        if (200 !== $response->getStatusCode()) {
            return false;
        }

        if ('XMLHttpRequest' === $request->headers->get('x-requested-with')) {
            return false;
        }

        return $this->isRequestDecorable($request);
    }

    public function isRequestDecorable(Request $request): bool
    {
        $route = $request->get('_route');

        return null !== $route && $this->isRouteNameDecorable($route) && $this->isRouteUriDecorable($request->getPathInfo());
    }

    public function isRouteNameDecorable(string $routeName): bool
    {
        foreach ($this->ignoreRoutes as $route) {
            if ($routeName === $route) {
                return false;
            }
        }

        foreach ($this->ignoreRoutePatterns as $routePattern) {
            if (1 === preg_match(sprintf('#%s#', $routePattern), $routeName)) {
                return false;
            }
        }

        return true;
    }

    public function isRouteUriDecorable(string $uri): bool
    {
        foreach ($this->ignoreUriPatterns as $uriPattern) {
            if (1 === preg_match(sprintf('#%s#', $uriPattern), $uri)) {
                return false;
            }
        }

        return true;
    }
}
