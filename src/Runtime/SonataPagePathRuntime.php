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

namespace Sonata\PageBundle\Runtime;

use Sonata\PageBundle\Request\SiteRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Runtime\Runner\Symfony\HttpKernelRunner;
use Symfony\Component\Runtime\RunnerInterface;
use Symfony\Component\Runtime\SymfonyRuntime;

final class SonataPagePathRuntime extends SymfonyRuntime
{
    public function getRunner(?object $application): RunnerInterface
    {
        if (!$application instanceof HttpKernelInterface) {
            return parent::getRunner($application);
        }

        Request::setFactory(
            /**
             * @param string|resource|null $content
             */
            static fn (
                array $query = [],
                array $request = [],
                array $attributes = [],
                array $cookies = [],
                array $files = [],
                array $server = [],
                $content = null
            ) => new SiteRequest($query, $request, $attributes, $cookies, $files, $server, $content)
        );

        return new HttpKernelRunner($application, SiteRequest::createFromGlobals());
    }
}
