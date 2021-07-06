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

namespace Sonata\PageBundle\Tests\Site;

use Sonata\PageBundle\CmsManager\DecoratorStrategy;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Request\SiteRequest;
use Sonata\PageBundle\Site\HostPathByLocaleSiteSelector;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author BadPixxel <eshop.bpaquier@gmail.com>
 */
class SubRequestsSiteSelectorTest extends BaseLocaleSiteSelectorTest
{
    protected function setUp(): void
    {
        $siteManager = $this->createMock(SiteManagerInterface::class);
        $decoratorStrategy = new DecoratorStrategy([], [], []);
        $seoPage = $this->createMock(SeoPageInterface::class);

        $this->siteSelector = $this->getMockBuilder(HostPathByLocaleSiteSelector::class)
            ->setConstructorArgs([$siteManager, $decoratorStrategy, $seoPage])
            ->setMethods(['getSites'])
            ->getMock();
    }

    /**
     * Tests onKernelRequest method with Master request detect default site.
     */
    public function testOnKernelRequestWithMasterDetectEn(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com');

        // Ensure request locale is null
        $this->assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->siteSelector
            ->expects($this->once())
            ->method('getSites')
            ->with($request)
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is still null
        $this->assertNull($request->attributes->get('_locale'));

        $site = $this->getSite();

        // Ensure no site was retrieved
        $this->assertNull($site);
    }

    /**
     * Tests onKernelRequest method with Master request detect /fr site.
     */
    public function testOnKernelRequestWithMasterDetectFr(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com/fr');

        // Ensure request locale is null
        $this->assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->siteSelector
            ->expects($this->once())
            ->method('getSites')
            ->with($request)
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is now fr
        $this->assertSame('fr', $request->attributes->get('_locale'));

        $site = $this->getSite();

        // Ensure /fr site was retrieved
        $this->assertNotEmpty($site);
        $this->assertSame('/fr', $site->getRelativePath());
    }

    /**
     * Tests onKernelRequest method with Sub request detect default site.
     */
    public function testOnKernelRequestWithSubDetectEn(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com');

        // Ensure request locale is null
        $this->assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $this->siteSelector
            ->expects($this->once())
            ->method('getSites')
            ->with($request)
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is still null
        $this->assertNull($request->attributes->get('_locale'));

        $site = $this->getSite();

        // Ensure no site was retrieved
        $this->assertNull($site);
    }

    /**
     * Tests onKernelRequest method with Sub request detect /fr site.
     */
    public function testOnKernelRequestWithSubDetectFr(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com/fr');

        // Ensure request locale is null
        $this->assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $this->siteSelector
            ->expects($this->once())
            ->method('getSites')
            ->with($request)
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is now fr
        $this->assertSame('fr', $request->attributes->get('_locale'));

        $site = $this->getSite();

        // Ensure /fr site was retrieved
        $this->assertNotEmpty($site);
        $this->assertSame('/fr', $site->getRelativePath());
    }
}
