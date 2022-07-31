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
    private SiteSelectorInterface $selector;

    private ?SiteInterface $site = null;

    /**
     * @param string $baseUrl
     * @param string $method
     * @param string $host
     * @param string $scheme
     * @param int    $httpPort
     * @param int    $httpsPort
     */
    public function __construct(SiteSelectorInterface $selector, $baseUrl = '', $method = 'GET', $host = 'localhost', $scheme = 'http', $httpPort = 80, $httpsPort = 443)
    {
        $this->selector = $selector;

        parent::__construct($baseUrl, $method, $host, $scheme, $httpPort, $httpsPort);
    }

    public function getHost(): string
    {
        $site = $this->getSite();

        if ($site && !$site->isLocalhost()) {
            return $site->getHost();
        }

        return parent::getHost();
    }

    public function getBaseUrl(): string
    {
        $site = $this->getSite();

        if ($site) {
            return parent::getBaseUrl().$site->getRelativePath();
        }

        return parent::getBaseUrl();
    }

    public function setSite(SiteInterface $site): void
    {
        $this->site = $site;
    }

    public function getSite()
    {
        if (!$this->site instanceof SiteInterface) {
            $this->site = $this->selector->retrieve();
        }

        return $this->site;
    }
}
