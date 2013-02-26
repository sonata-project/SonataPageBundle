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

/**
 * SiteRequestInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SiteRequestInterface
{
    /**
     * @param string $pathInfo
     *
     * @return void
     */
    public function setPathInfo($pathInfo);

    /**
     * @param string $baseUrl
     *
     * @return void
     */
    public function setBaseUrl($baseUrl);
}
