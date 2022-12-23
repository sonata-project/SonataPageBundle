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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

final class ExceptionListenerTest extends TestCase
{
    /**
     * @var MockObject&SiteSelectorInterface
     */
    private SiteSelectorInterface $siteSelector;

    /**
     * @var MockObject&Environment
     */
    private Environment $twig;

    /**
     * @var MockObject&DecoratorStrategyInterface
     */
    private DecoratorStrategyInterface $decoratorStrategy;

    /**
     * @var MockObject&PageServiceManagerInterface
     */
    private PageServiceManagerInterface $pageServiceManager;

    /**
     * @var MockObject&CmsManagerSelectorInterface
     */
    private CmsManagerSelectorInterface $cmsSelector;

    /**
     * @var MockObject&LoggerInterface
     */
    private LoggerInterface $logger;

    private ExceptionListener $listener;

    /**
     * setup unit test.
     */
    protected function setUp(): void
    {
        // mock dependencies
        $this->siteSelector = $this->createMock(SiteSelectorInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $this->pageServiceManager = $this->createMock(PageServiceManagerInterface::class);
        $this->cmsSelector = $this->createMock(CmsManagerSelectorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $errors = [
            '404' => 'route_404',
            '403' => 'route_403',
        ];

        $this->listener = new ExceptionListener(
            $this->siteSelector,
            $this->cmsSelector,
            false,
            $this->twig,
            $this->pageServiceManager,
            $this->decoratorStrategy,
            $errors,
            $this->logger
        );
    }

    public function testInternalException(): void
    {
        $exception = new InternalErrorException();
        $event = $this->getMockEvent($exception);

        $this->logger->expects(static::once())->method('error');

        $this->listener->onKernelException($event);
    }

    public function testNotFoundExceptionInEditorMode(): void
    {
        $exception = new NotFoundHttpException();
        $event = $this->getMockEvent($exception);

        // mocked cms selector should enable editor mode
        $this->cmsSelector->expects(static::once())->method('isEditor')->willReturn(true);

        // mocked decorator strategy should allow decorate
        $this->decoratorStrategy->expects(static::once())->method('isRouteUriDecorable')->willReturn(true);

        // mock twig to expect a twig rendering
        $this->twig->expects(static::once())->method('render')
             ->with(static::equalTo('@SonataPage/Page/create.html.twig'));

        $this->listener->onKernelException($event);

        $response = $event->getResponse();

        static::assertNotNull($response);
        static::assertSame(404, $response->getStatusCode(), 'Should return 404 status code');
    }

    public function testNotFoundException(): void
    {
        $exception = $this->createMock(NotFoundHttpException::class);
        $exception->method('getStatusCode')->willReturn(404);
        $event = $this->getMockEvent($exception);

        static::assertSame('en', $event->getRequest()->getLocale());

        // mock a site
        $site = $this->createMock(SiteInterface::class);
        $site->expects(static::once())->method('getLocale')->willReturn('fr');

        // mock an error page
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getSite')->willReturn($site);

        // mock cms manager to return the mock error page and set it as current page
        $cmsManager = $this->createMock(CmsManagerInterface::class);
        $cmsManager
            ->expects(static::once())
            ->method('getPageByRouteName')
            ->with(static::anything(), static::equalTo('route_404'))
            ->willReturn($page);
        $cmsManager->expects(static::once())->method('setCurrentPage')->with(static::equalTo($page));
        $this->cmsSelector->method('retrieve')->willReturn($cmsManager);

        // mocked site selector should return a site
        $this->siteSelector
            ->method('retrieve')
            ->willReturn($this->createMock(SiteInterface::class));

        // mocked decorator strategy should allow decorate
        $this->decoratorStrategy
            ->method('isRouteNameDecorable')
            ->willReturn(true);
        $this->decoratorStrategy
            ->method('isRouteUriDecorable')
            ->willReturn(true);

        // mocked page service manager should execute the page and return a response
        $response = $this->createMock(Response::class);
        $this->pageServiceManager
            ->expects(static::once())
            ->method('execute')
            ->with(static::equalTo($page))
            ->willReturn($response);

        $this->listener->onKernelException($event);

        static::assertSame('fr', $event->getRequest()->getLocale());
    }

    private function getMockEvent(\Exception $exception): ExceptionEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        // TODO: Simplify this when dropping support for Symfony <  5.3
        $mainRequestType = \defined(HttpKernelInterface::class.'::MAIN_REQUEST') ? HttpKernelInterface::MAIN_REQUEST : 1;

        return new ExceptionEvent($kernel, $request, $mainRequestType, $exception);
    }
}
