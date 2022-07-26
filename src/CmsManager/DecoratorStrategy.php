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
 * The DecoratorStrategy class defines if a request can be decorate by a PageInterface depends
 * on the current request.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class DecoratorStrategy implements DecoratorStrategyInterface
{
    private array $ignoreRoutes;

    private array $ignoreRoutePatterns;

    private array $ignoreUriPatterns;

    public function __construct(array $ignoreRoutes, array $ignoreRoutePatterns, array $ignoreUriPatterns)
    {
        $this->ignoreRoutes = $ignoreRoutes;
        $this->ignoreRoutePatterns = $ignoreRoutePatterns;
        $this->ignoreUriPatterns = $ignoreUriPatterns;
    }

    public function isDecorable(Request $request, $requestType, Response $response)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $requestType) {
            return false;
        }

        if ('text/html' !== substr($response->headers->get('Content-Type') ?: 'text/html', 0, 9)) {
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

    public function isRequestDecorable(Request $request)
    {
        return $this->isRouteNameDecorable($request->get('_route')) && $this->isRouteUriDecorable($request->getPathInfo());
    }

    public function isRouteNameDecorable($routeName)
    {
        if (!$routeName) {
            return false;
        }

        foreach ($this->ignoreRoutes as $route) {
            if ($routeName === $route) {
                return false;
            }
        }

        foreach ($this->ignoreRoutePatterns as $routePattern) {
            if (preg_match(sprintf('#%s#', $routePattern), $routeName)) {
                return false;
            }
        }

        return true;
    }

    public function isRouteUriDecorable($uri)
    {
        foreach ($this->ignoreUriPatterns as $uriPattern) {
            if (preg_match(sprintf('#%s#', $uriPattern), $uri)) {
                return false;
            }
        }

        return true;
    }
}
