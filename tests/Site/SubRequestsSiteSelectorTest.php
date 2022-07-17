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

use PHPUnit\Framework\MockObject\MockObject;
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
final class SubRequestsSiteSelectorTest extends BaseLocaleSiteSelectorTest
{
    /**
     * @var MockObject&SiteManagerInterface
     */
    private $siteManager;

    private HostPathByLocaleSiteSelector $siteSelector;

    protected function setUp(): void
    {
        $this->siteManager = $this->createMock(SiteManagerInterface::class);
        $decoratorStrategy = new DecoratorStrategy([], [], []);
        $seoPage = $this->createMock(SeoPageInterface::class);

        $this->siteSelector = new HostPathByLocaleSiteSelector(
            $this->siteManager,
            $decoratorStrategy,
            $seoPage
        );
    }

    /**
     * Tests onKernelRequest method with Master request detect default site.
     */
    public function testOnKernelRequestWithMasterDetectEn(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com');

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->siteManager
            ->expects(static::once())
            ->method('findBy')
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is still null
        static::assertNull($request->attributes->get('_locale'));

        $site = $this->siteSelector->retrieve();

        // Ensure no site was retrieved
        static::assertNull($site);
    }

    /**
     * Tests onKernelRequest method with Master request detect /fr site.
     */
    public function testOnKernelRequestWithMasterDetectFr(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com/fr');

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->siteManager
            ->expects(static::once())
            ->method('findBy')
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is now fr
        static::assertSame('fr', $request->attributes->get('_locale'));

        $site = $this->siteSelector->retrieve();

        // Ensure /fr site was retrieved
        static::assertNotEmpty($site);
        static::assertSame('/fr', $site->getRelativePath());
    }

    /**
     * Tests onKernelRequest method with Sub request detect default site.
     */
    public function testOnKernelRequestWithSubDetectEn(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com');

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $this->siteManager
            ->expects(static::once())
            ->method('findBy')
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is still null
        static::assertNull($request->attributes->get('_locale'));

        $site = $this->siteSelector->retrieve();

        // Ensure no site was retrieved
        static::assertNull($site);
    }

    /**
     * Tests onKernelRequest method with Sub request detect /fr site.
     */
    public function testOnKernelRequestWithSubDetectFr(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com/fr');

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::SUB_REQUEST);

        $this->siteManager
            ->expects(static::once())
            ->method('findBy')
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is now fr
        static::assertSame('fr', $request->attributes->get('_locale'));

        $site = $this->siteSelector->retrieve();

        // Ensure /fr site was retrieved
        static::assertNotEmpty($site);
        static::assertSame('/fr', $site->getRelativePath());
    }
}
