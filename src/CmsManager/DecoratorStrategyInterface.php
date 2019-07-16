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

/**
 * This interface defines if a request can be decorate by a PageInterface depends
 * on the current request.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface DecoratorStrategyInterface
{
    /**
     * @param int $requestType
     *
     * @return bool
     */
    public function isDecorable(Request $request, $requestType, Response $response);

    /**
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
