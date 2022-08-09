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
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Request\SiteRequest;
use Sonata\PageBundle\Site\HostPathByLocaleSiteSelector;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Rémi Marseille <marseille@ekino.com>
 */
final class HostPathByLocaleSiteSelectorTest extends BaseLocaleSiteSelectorTest
{
    /**
     * @var MockObject&SiteManagerInterface
     */
    private $siteManager;

    private HostPathByLocaleSiteSelector $siteSelector;

    protected function setUp(): void
    {
        $this->siteManager = $this->createMock(SiteManagerInterface::class);
        $decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $seoPage = $this->createMock(SeoPageInterface::class);

        $this->siteSelector = new HostPathByLocaleSiteSelector(
            $this->siteManager,
            $decoratorStrategy,
            $seoPage
        );
    }

    public function testHandleKernelRequestRedirectsToEn(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com');

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        // TODO: Simplify this when dropping support for Symfony <  5.3
        $mainRequestType = \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : 1;

        $event = new RequestEvent($kernel, $request, $mainRequestType);

        $this->siteManager
            ->expects(static::once())
            ->method('findBy')
            ->willReturn($this->getSites());

        $this->siteSelector->handleKernelRequest($event);

        // Ensure request locale is still null
        static::assertNull($request->attributes->get('_locale'));

        $site = $this->siteSelector->retrieve();

        // Ensure no site was retrieved
        static::assertNull($site);

        // Retrieve the event's response object
        $response = $event->getResponse();

        // Ensure the response was a redirect to the default site
        static::assertInstanceOf(RedirectResponse::class, $response);

        // Ensure the redirect url is for "/en"
        static::assertSame('/en', $response->getTargetUrl());
    }

    public function testHandleKernelRequestRedirectsToFr(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
        ]);

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        // TODO: Simplify this when dropping support for Symfony <  5.3
        $mainRequestType = \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : 1;

        $event = new RequestEvent($kernel, $request, $mainRequestType);

        $this->siteManager
            ->expects(static::once())
            ->method('findBy')
            ->willReturn($this->getSites());

        $this->siteSelector->handleKernelRequest($event);

        // Ensure request locale is still null
        static::assertNull($request->attributes->get('_locale'));

        $site = $this->siteSelector->retrieve();

        // Ensure no site was retrieved
        static::assertNull($site);

        // Retrieve the event's response object
        $response = $event->getResponse();

        // Ensure the response was a redirect to the default site
        static::assertInstanceOf(RedirectResponse::class, $response);

        // Ensure the redirect url is for "/fr"
        static::assertSame('/fr', $response->getTargetUrl());
    }
}
