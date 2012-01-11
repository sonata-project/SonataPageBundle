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

interface SiteRequestInterface
{
    /**
     * @param $pathInfo
     * @return void
     */
    function setPathInfo($pathInfo);

    /**
     * @param $baseUrl
     * @return void
     */
    function setBaseUrl($baseUrl);
}