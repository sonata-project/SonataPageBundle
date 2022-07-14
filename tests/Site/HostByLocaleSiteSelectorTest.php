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
use Sonata\PageBundle\Site\HostByLocaleSiteSelector;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Rémi Marseille <marseille@ekino.com>
 */
final class HostByLocaleSiteSelectorTest extends BaseLocaleSiteSelectorTest
{
    /**
     * @var MockObject&SiteManagerInterface
     */
    private $siteManager;

    private HostByLocaleSiteSelector $siteSelector;

    protected function setUp(): void
    {
        $this->siteManager = $this->createMock(SiteManagerInterface::class);
        $decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $seoPage = $this->createMock(SeoPageInterface::class);

        $this->siteSelector = new HostByLocaleSiteSelector(
            $this->siteManager,
            $decoratorStrategy,
            $seoPage
        );
    }

    /**
     * Tests handleKernelRequest method selects the site /en.
     */
    public function testHandleKernelRequestSelectsEn(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com');

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->siteManager
            ->expects(static::once())
            ->method('findBy')
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is en
        static::assertSame('en', $request->attributes->get('_locale'));

        $site = $this->siteSelector->retrieve();

        // Ensure we retrieved the site "/en"
        static::assertSame('/en', $site->getRelativePath());
    }

    /**
     * Tests handleKernelRequest method selects the site /fr.
     */
    public function testHandleKernelRequestSelectsFr(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
        ]);

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        $event = new RequestEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->siteManager
            ->expects(static::once())
            ->method('findBy')
            ->willReturn($this->getSites());

        $this->siteSelector->onKernelRequest($event);

        // Ensure request locale is fr
        static::assertSame('fr', $request->attributes->get('_locale'));

        $site = $this->siteSelector->retrieve();

        // Ensure we retrieved the site "/fr"
        static::assertSame('/fr', $site->getRelativePath());
    }
}
