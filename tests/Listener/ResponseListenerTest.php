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
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Listener\ResponseListener;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

final class ResponseListenerTest extends TestCase
{
    /**
     * @var MockObject&DecoratorStrategyInterface
     */
    private $decoratorStrategy;

    /**
     * @var MockObject&PageServiceManagerInterface
     */
    private $pageServiceManager;

    /**
     * @var MockObject&CmsManagerInterface
     */
    private $cmsManager;

    /**
     * @var MockObject&CmsManagerSelectorInterface
     */
    private $cmsSelector;

    /**
     * @var MockObject&Environment
     */
    private $twig;

    private ResponseListener $listener;

    /**
     * setup unit test.
     */
    protected function setUp(): void
    {
        $this->decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $this->pageServiceManager = $this->createMock(PageServiceManagerInterface::class);
        $this->cmsManager = $this->createMock(CmsManagerInterface::class);
        $this->cmsSelector = $this->createMock(CmsManagerSelectorInterface::class);
        $this->cmsSelector->expects(static::once())->method('retrieve')->willReturn($this->cmsManager);
        $this->twig = $this->createMock(Environment::class);

        $this->listener = new ResponseListener(
            $this->cmsSelector,
            $this->pageServiceManager,
            $this->decoratorStrategy,
            $this->twig,
            true
        );
    }

    /**
     * Test the listener without a page.
     */
    public function testWithoutPage(): void
    {
        $this->expectException(InternalErrorException::class);

        // mocked decorator strategy should accept to decorate
        $this->decoratorStrategy->expects(static::once())->method('isDecorable')->willReturn(true);

        // mocked cms manager should return the mock page
        $this->cmsManager->expects(static::once())->method('getCurrentPage')->willReturn(null);

        $this->listener->onCoreResponse(
            $this->getMockEvent('content')
        );
    }

    /**
     * Test that the  listener does not mess up with response when a page is non decorable.
     */
    public function testPageIsNonDecorable(): void
    {
        $this->decoratorStrategy->expects(static::once())->method('isDecorable')->willReturn(false);

        $event = $this->getMockEvent('content');

        $this->listener->onCoreResponse($event);

        static::assertSame(
            'content',
            $event->getResponse()->getContent(),
            'response should not be altered when non-decorable'
        );
    }

    /**
     * Test that the listener correctly decorates the response content when a page is decorable.
     */
    public function testPageIsDecorable(): void
    {
        // a response content
        $content = 'inner';

        // a mock page entity that accepts to be decorated
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('isHybrid')->willReturn(true);
        $page->expects(static::once())->method('getDecorate')->willReturn(true);

        // mocked cms manager should return the mock page
        $this->cmsManager->expects(static::once())->method('getCurrentPage')->willReturn($page);

        // mocked decorator strategy should accept to decorate
        $this->decoratorStrategy->expects(static::once())->method('isDecorable')->willReturn(true);

        // a mock page service manager that decorates a response
        $this->pageServiceManager->expects(static::once())
            ->method('execute')
            ->with(static::equalTo($page), static::anything(), ['content' => $content])
            ->willReturnCallback(static function (PageInterface $page, Request $request, array $params, Response $response) {
                $response->setContent(sprintf('outer "%s" outer', $params['content']));

                return $response;
            });

        // create a response event
        $event = $this->getMockEvent($content);

        $this->listener->onCoreResponse($event);

        static::assertSame('outer "inner" outer', $event->getResponse()->getContent());
    }

    /**
     * Test that the listener correctly alters the http headers when the editor is enabled.
     */
    public function testPageIsEditor(): void
    {
        $this->cmsSelector->expects(static::once())->method('isEditor')->willReturn(true);
        $event = $this->getMockEvent('inner');

        $this->listener->onCoreResponse($event);

        static::assertFalse($event->getResponse()->isCacheable(), 'Should not be cacheable in editor mode');

        // assert a cookie has been set in the response headers
        $cookies = $event->getResponse()->headers->getCookies();
        $foundCookie = false;
        foreach ($cookies as $cookie) {
            if ('sonata_page_is_editor' === $cookie->getName()) {
                static::assertSame('1', $cookie->getValue());
                $foundCookie = true;
            }
        }

        static::assertTrue($foundCookie, 'Should have found the editor mode cookie');
    }

    /**
     * Returns a mocked event with given content data.
     */
    private function getMockEvent(string $content): ResponseEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();
        $response = new Response($content);

        return new ResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);
    }
}
