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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * This interface defines if a request can be decorate by a PageInterface depends
 * on the current request
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface DecoratorStrategyInterface
{
    /**
     * @param Request  $request
     * @param integer  $requestType
     * @param Response $response
     *
     * @return boolean
     */
    public function isDecorable(Request $request, $requestType, Response $response);

    /**
     * @param Request $request
     *
     * @return boolean
     */
    public function isRequestDecorable(Request $request);

    /**
     * @param string $routeName
     *
     * @return boolean
     */
    public function isRouteNameDecorable($routeName);

    /**
     * @param string $uri
     *
     * @return boolean
     */
    public function isRouteUriDecorable($uri);
}
