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

use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Request\SiteRequest;
use Sonata\PageBundle\Site\HostPathByLocaleSiteSelector;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Tests the HostPathByLocaleSiteSelector service.
 *
 * @author Rémi Marseille <marseille@ekino.com>
 */
class HostPathByLocaleSiteSelectorTest extends BaseLocaleSiteSelectorTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $siteManager = $this->createMock(SiteManagerInterface::class);
        $decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $seoPage = $this->createMock(SeoPageInterface::class);

        $this->siteSelector = $this->getMockBuilder(HostPathByLocaleSiteSelector::class)
            ->setConstructorArgs([$siteManager, $decoratorStrategy, $seoPage])
            ->setMethods(['getSites'])
            ->getMock();
    }

    /**
     * Tests handleKernelRequest method redirects to /en.
     */
    public function testHandleKernelRequestRedirectsToEn(): void
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
            ->will($this->returnValue($this->getSites()));

        $this->siteSelector->handleKernelRequest($event);

        // Ensure request locale is still null
        $this->assertNull($request->attributes->get('_locale'));

        $site = $this->getSite();

        // Ensure no site was retrieved
        $this->assertNull($site);

        // Retrieve the event's response object
        $response = $event->getResponse();

        // Ensure the response was a redirect to the default site
        $this->assertInstanceOf(RedirectResponse::class, $response);

        // Ensure the redirect url is for "/en"
        $this->assertEquals('/en', $response->getTargetUrl());
    }

    /**
     * Tests handleKernelRequest method redirects to /fr.
     */
    public function testHandleKernelRequestRedirectsToFr(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
        ]);

        // Ensure request locale is null
        $this->assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->siteSelector
            ->expects($this->once())
            ->method('getSites')
            ->with($request)
            ->will($this->returnValue($this->getSites()));

        $this->siteSelector->handleKernelRequest($event);

        // Ensure request locale is still null
        $this->assertNull($request->attributes->get('_locale'));

        $site = $this->getSite();

        // Ensure no site was retrieved
        $this->assertNull($site);

        // Retrieve the event's response object
        $response = $event->getResponse();

        // Ensure the response was a redirect to the default site
        $this->assertInstanceOf(RedirectResponse::class, $response);

        // Ensure the redirect url is for "/fr"
        $this->assertEquals('/fr', $response->getTargetUrl());
    }
}
