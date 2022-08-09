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
    public function isDecorable(Request $request, int $requestType, Response $response): bool;

    public function isRequestDecorable(Request $request): bool;

    public function isRouteNameDecorable(string $routeName): bool;

    public function isRouteUriDecorable(string $uri): bool;
}
