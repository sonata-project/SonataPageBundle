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

use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Twig\Extension\PageExtension;

/**
 * This is the PageExtension test class
 */
class PageExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ajaxUrl() PageExtension method
     */
    public function testAjaxUrl()
    {
        $blockManager = $this->getMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockInteractor = $this->getMock('Sonata\PageBundle\Model\BlockInteractorInterface');
        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())->method('generate')->will($this->returnValue('/foo/bar'));
        $blockHelper = $this->getMockBuilder('Sonata\BlockBundle\Templating\Helper\BlockHelper')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $HttpKernelExtension = $this->getMockBuilder('Symfony\Bridge\Twig\Extension\HttpKernelExtension')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $block = $this->getMock('Sonata\PageBundle\Model\PageBlockInterface');
        $block->expects($this->exactly(2))->method('getPage')->will($this->returnValue($page));

        $extension = new PageExtension($cmsManager, $siteSelector, $router, $blockHelper, $blockManager, $blockInteractor, $HttpKernelExtension);
        $this->assertEquals('/foo/bar', $extension->ajaxUrl($block));
    }
}
