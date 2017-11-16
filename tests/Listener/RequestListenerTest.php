<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Listener;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Listener\RequestListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Test the page bundle request listener.
 */
class RequestListenerTest extends TestCase
{
    public function testValidSite()
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getEnabled')->will($this->returnValue(true));

        $seoPage = $this->createMock('Sonata\SeoBundle\Seo\SeoPageInterface');

        $decoratorStrategy = $this->createMock('Sonata\PageBundle\CmsManager\DecoratorStrategyInterface');
        $decoratorStrategy->expects($this->once())->method('isRequestDecorable')->will($this->returnValue(true));

        $cmsManager = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->once())->method('getPageByRouteName')->will($this->returnValue($page));

        $cmsSelector = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');

        $siteSelector = $this->createMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->once())->method('retrieve')->will($this->returnValue($site));

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');
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
        $cmsManager = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');

        $seoPage = $this->createMock('Sonata\SeoBundle\Seo\SeoPageInterface');

        $decoratorStrategy = $this->createMock('Sonata\PageBundle\CmsManager\DecoratorStrategyInterface');
        $decoratorStrategy->expects($this->once())->method('isRequestDecorable')->will($this->returnValue(true));

        $cmsSelector = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $siteSelector = $this->createMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->once())->method('retrieve')->will($this->returnValue(false));

        $kernel = $this->createMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, 'master');

        $listener = new RequestListener($cmsSelector, $siteSelector, $decoratorStrategy, $seoPage);
        $listener->onCoreRequest($event);
    }
}
