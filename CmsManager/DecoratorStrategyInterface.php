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

interface DecoratorStrategyInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $requestType
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return boolean
     */
    function isDecorable(Request $request, $requestType, Response $response);

    /**
     * @param $routeName
     * @return boolean
     */
    function isRouteNameDecorable($routeName);

    /**
     * @param $uri
     * @return boolean
     */
    function isRouteUriDecorable($uri);
}