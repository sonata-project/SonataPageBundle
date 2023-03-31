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

use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SiteRequestContext extends RequestContext implements SiteRequestContextInterface
{
    private ?SiteInterface $site = null;

    public function __construct(
        private SiteSelectorInterface $selector,
        string $baseUrl = '',
        string $method = 'GET',
        string $host = 'localhost',
        string $scheme = 'http',
        int $httpPort = 80,
        int $httpsPort = 443
    ) {
        parent::__construct($baseUrl, $method, $host, $scheme, $httpPort, $httpsPort);
    }

    public function getHost(): string
    {
        $site = $this->getSite();

        if (null !== $site && !$site->isLocalhost()) {
            $host = $site->getHost();

            if (null === $host) {
                throw new \LogicException('The host must be defined');
            }

            return $host;
        }

        return parent::getHost();
    }

    public function getBaseUrl(): string
    {
        $site = $this->getSite();

        if (null !== $site) {
            return parent::getBaseUrl().$site->getRelativePath();
        }

        return parent::getBaseUrl();
    }

    public function setSite(?SiteInterface $site): void
    {
        $this->site = $site;
    }

    public function getSite(): ?SiteInterface
    {
        if (!$this->site instanceof SiteInterface) {
            $this->site = $this->selector->retrieve();
        }

        return $this->site;
    }
}
