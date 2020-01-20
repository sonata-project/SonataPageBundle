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
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;

class ExceptionListenerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $decoratorStrategy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageServiceManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmsManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmsSelector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $siteSelector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templating;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var ExceptionListener
     */
    protected $listener;

    /**
     * setup unit test.
     */
    protected function setUp(): void
    {
        // mock dependencies
        $this->siteSelector = $this->createMock(SiteSelectorInterface::class);
        $this->cmsSelector = $this->createMock(CmsManagerSelectorInterface::class);
        $this->templating = $this->createMock(EngineInterface::class);
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
            $this->templating,
            $this->pageServiceManager,
            $this->decoratorStrategy,
            $errors,
            $this->logger
        );
    }

    /**
     * Test an internal exception.
     */
    public function testInternalException()
    {
        $exception = $this->createMock(InternalErrorException::class);
        $event = $this->getMockEvent($exception);

        $this->logger->expects($this->once())->method('error');

        $this->listener->onKernelException($event);
    }

    /**
     * Test the not found exception in editor mode.
     */
    public function testNotFoundExceptionInEditorMode()
    {
        $exception = new NotFoundHttpException();
        $event = $this->getMockEvent($exception);

        // mocked cms selector should enable editor mode
        $this->cmsSelector->expects($this->once())->method('isEditor')->willReturn(true);

        // mocked decorator strategy should allow decorate
        $this->decoratorStrategy->expects($this->once())->method('isRouteUriDecorable')->willReturn(true);

        // mock templating to expect a twig rendering
        $this->templating->expects($this->once())->method('render')
             ->with($this->equalTo('@SonataPage/Page/create.html.twig'));

        $this->listener->onKernelException($event);

        $this->assertInstanceOf(Response::class, $event->getResponse(), 'Should return a response in event');
        $this->assertSame(404, $event->getResponse()->getStatusCode(), 'Should return 404 status code');
    }

    /**
     * Test the not found exception rendering.
     */
    public function testNotFoundException()
    {
        $exception = $this->createMock(NotFoundHttpException::class);
        $exception->expects($this->any())->method('getStatusCode')->willReturn(404);
        $event = $this->getMockEvent($exception);

        $this->assertSame('en', $event->getRequest()->getLocale());

        // mock a site
        $site = $this->createMock(SiteInterface::class);
        $site->expects($this->exactly(3))->method('getLocale')->willReturn('fr');

        // mock an error page
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(3))->method('getSite')->willReturn($site);

        // mock cms manager to return the mock error page and set it as current page
        $this->cmsManager = $this->createMock(CmsManagerInterface::class);
        $this->cmsManager
            ->expects($this->once())
            ->method('getPageByRouteName')
            ->with($this->anything(), $this->equalTo('route_404'))
            ->willReturn($page);
        $this->cmsManager->expects($this->once())->method('setCurrentPage')->with($this->equalTo($page));
        $this->cmsSelector->expects($this->any())->method('retrieve')->willReturn($this->cmsManager);

        // mocked site selector should return a site
        $this->siteSelector
            ->expects($this->any())
            ->method('retrieve')
            ->willReturn($this->createMock(SiteInterface::class));

        // mocked decorator strategy should allow decorate
        $this->decoratorStrategy
            ->expects($this->any())
            ->method('isRouteNameDecorable')
            ->willReturn(true);
        $this->decoratorStrategy
            ->expects($this->any())
            ->method('isRouteUriDecorable')
            ->willReturn(true);

        // mocked page service manager should execute the page and return a response
        $response = $this->createMock(Response::class);
        $this->pageServiceManager
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($page))
            ->willReturn($response);

        $this->listener->onKernelException($event);

        $this->assertSame('fr', $event->getRequest()->getLocale());
    }

    /**
     * Returns a mocked event with given content data.
     */
    protected function getMockEvent(\Exception $exception): GetResponseForExceptionEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        return new GetResponseForExceptionEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $exception);
    }
}
