<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\CmsManager;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * The DecoratorStrategy class defines if a request can be decorate by a PageInterface depends
 * on the current request.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class DecoratorStrategy implements DecoratorStrategyInterface
{
    protected $ignoreRoutes;

    protected $ignoreRoutePatterns;

    protected $ignoreUriPatterns;

    /**
     * @param array $ignoreRoutes
     * @param array $ignoreRoutePatterns
     * @param array $ignoreUriPatterns
     */
    public function __construct(array $ignoreRoutes, array $ignoreRoutePatterns, array $ignoreUriPatterns)
    {
        $this->ignoreRoutes        = $ignoreRoutes;
        $this->ignoreRoutePatterns = $ignoreRoutePatterns;
        $this->ignoreUriPatterns   = $ignoreUriPatterns;
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorable(Request $request, $requestType, Response $response)
    {
        if ($requestType != HttpKernelInterface::MASTER_REQUEST) {
            return false;
        }

        if ((substr($response->headers->get('Content-Type') ? : 'text/html', 0, 9)) != 'text/html') {
            return false;
        }

        if ($response->headers->get('x-sonata-page-not-decorable', false) === true) {
            return false;
        }

        // the main controller explicitly force the the page to be decorate
        if ($response->headers->get('x-sonata-page-decorable', false) === true) {
            return true;
        }

        if ($response->getStatusCode() != 200) {
            return false;
        }

        if ($request->headers->get('x-requested-with') == 'XMLHttpRequest') {
            return false;
        }

        return $this->isRequestDecorable($request);
    }

    /**
     * {@inheritdoc}
     */
    public function isRequestDecorable(Request $request)
    {
        return $this->isRouteNameDecorable($request->get('_route')) && $this->isRouteUriDecorable($request->getPathInfo());
    }

    /**
     * {@inheritdoc}
     */
    public function isRouteNameDecorable($routeName)
    {
        if (!$routeName) {
            return false;
        }

        foreach ($this->ignoreRoutes as $route) {
            if ($routeName == $route) {
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

    /**
     * {@inheritdoc}
     */
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
