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

namespace Sonata\PageBundle\Request;

use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SiteRequest extends BaseRequest implements SiteRequestInterface
{
    public function setPathInfo(string $pathInfo): void
    {
        $this->pathInfo = $pathInfo;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }
}
