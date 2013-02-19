<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Twig\Extension;

use Sonata\PageBundle\Twig\Extension\PageExtension;
use Sonata\PageBundle\Model\PageInterface;

/**
 *
 */
class PageExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testUrl()
    {
        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $extension = new PageExtension($cmsManager, $siteSelector, $router);
        $extension->url('sd');
    }

    public function testAjaxUrl()
    {
        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->will($this->returnValue('/foo/bar'));

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $block = $this->getMock('Sonata\PageBundle\Model\PageBlockInterface');
        $block->expects($this->exactly(2))->method('getPage')->will($this->returnValue($page));

        $extension = new PageExtension($cmsManager, $siteSelector, $router);
        $this->assertEquals('/foo/bar', $extension->ajaxUrl($block));
    }
}
