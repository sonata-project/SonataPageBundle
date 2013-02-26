<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Listener;

use Sonata\PageBundle\Listener\RequestListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test the page bundle request listener
 */
class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidSite()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getEnabled')->will($this->returnValue(true));

        $seoPage = $this->getMock('Sonata\SeoBundle\Seo\SeoPageInterface');

        $decoratorStrategy = $this->getMock('Sonata\PageBundle\CmsManager\DecoratorStrategyInterface');
        $decoratorStrategy->expects($this->once())->method('isRequestDecorable')->will($this->returnValue(true));

        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->once())->method('getPageByRouteName')->will($this->returnValue($page));

        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->once())->method('retrieve')->will($this->returnValue($site));

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, 'master');

        $listener = new RequestListener($cmsSelector, $siteSelector, $decoratorStrategy, $seoPage);
        $listener->onCoreRequest($event);
    }

    /**
     * @expectedException \Sonata\PageBundle\Exception\InternalErrorException
     */
    public function testNoSite()
    {
        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');

        $seoPage = $this->getMock('Sonata\SeoBundle\Seo\SeoPageInterface');

        $decoratorStrategy = $this->getMock('Sonata\PageBundle\CmsManager\DecoratorStrategyInterface');
        $decoratorStrategy->expects($this->once())->method('isRequestDecorable')->will($this->returnValue(true));

        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->once())->method('retrieve')->will($this->returnValue(false));

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, 'master');

        $listener = new RequestListener($cmsSelector, $siteSelector, $decoratorStrategy, $seoPage);
        $listener->onCoreRequest($event);
    }
}
