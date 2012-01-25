<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Request;

use Symfony\Component\Routing\RequestContext;
use Sonata\PageBundle\Site\SiteSelectorInterface;

class SiteRequestContext extends RequestContext
{
    protected $selector;

    /**
     * @param \Sonata\PageBundle\Site\SiteSelectorInterface $site
     * @param string $baseUrl
     * @param string $method
     * @param string $host
     * @param string $scheme
     * @param int $httpPort
     * @param int $httpsPort
     */
    public function __construct(SiteSelectorInterface $selector, $baseUrl = '', $method = 'GET', $host = 'localhost', $scheme = 'http', $httpPort = 80, $httpsPort = 443)
    {
        $this->selector = $selector;

        parent::__construct($baseUrl, $method, $host, $scheme, $httpPort, $httpsPort);
    }

    /**
     * @return string|void
     */
    public function getHost()
    {
        $site = $this->selector->retrieve();

        if ($site && !$site->isLocalhost()) {
            return $site->getHost();
        }

        return parent::getHost();
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        $site = $this->selector->retrieve();

        if ($site && !$site->isLocalhost()) {
            return parent::getBaseUrl().$site->getRelativePath();
        }

        return parent::getBaseUrl();
    }
}