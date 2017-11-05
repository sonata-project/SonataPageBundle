<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Request;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Request\SiteRequest;

class SiteRequestTest extends TestCase
{
    public function testSiteRequest()
    {
        $request = new SiteRequest();
        $request->setBaseUrl('folder/app_dev.php');
        $request->setPathInfo('/path-info');

        $this->assertEquals('folder/app_dev.php', $request->getBaseUrl());
        $this->assertEquals('/path-info', $request->getPathInfo());
    }
}
