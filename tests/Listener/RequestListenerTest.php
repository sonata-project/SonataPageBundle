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

namespace Sonata\PageBundle\Tests\Listener;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Listener\RequestListener;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestListenerTest extends TestCase
{
    public function testValidSite(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('getEnabled')->willReturn(true);

        $seoPage = $this->createMock(SeoPageInterface::class);

        $decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $decoratorStrategy->expects($this->once())->method('isRequestDecorable')->willReturn(true);

        $cmsManager = $this->createMock(CmsManagerInterface::class);
        $cmsManager->expects($this->once())->method('getPageByRouteName')->willReturn($page);

        $cmsSelector = $this->createMock(CmsManagerSelectorInterface::class);
        $cmsSelector->expects($this->once())->method('retrieve')->willReturn($cmsManager);

        $site = $this->createMock(SiteInterface::class);

        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $siteSelector->expects($this->once())->method('retrieve')->willReturn($site);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener($cmsSelector, $siteSelector, $decoratorStrategy, $seoPage);
        $listener->onCoreRequest($event);
    }

    public function testNoSite(): void
    {
        $this->expectException(InternalErrorException::class);

        $cmsManager = $this->createMock(CmsManagerInterface::class);

        $seoPage = $this->createMock(SeoPageInterface::class);

        $decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $decoratorStrategy->expects($this->once())->method('isRequestDecorable')->willReturn(true);

        $cmsSelector = $this->createMock(CmsManagerSelectorInterface::class);
        $cmsSelector->expects($this->once())->method('retrieve')->willReturn($cmsManager);

        $siteSelector = $this->createMock(SiteSelectorInterface::class);
        $siteSelector->expects($this->once())->method('retrieve')->willReturn(false);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $listener = new RequestListener($cmsSelector, $siteSelector, $decoratorStrategy, $seoPage);
        $listener->onCoreRequest($event);
    }
}
