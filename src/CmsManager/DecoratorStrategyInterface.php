<?php

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

/**
 * This interface defines if a request can be decorate by a PageInterface depends
 * on the current request.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface DecoratorStrategyInterface
{
    /**
     * @param Request  $request
     * @param int      $requestType
     * @param Response $response
     *
     * @return bool
     */
    public function isDecorable(Request $request, $requestType, Response $response);

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isRequestDecorable(Request $request);

    /**
     * @param string $routeName
     *
     * @return bool
     */
    public function isRouteNameDecorable($routeName);

    /**
     * @param string $uri
     *
     * @return bool
     */
    public function isRouteUriDecorable($uri);
}
