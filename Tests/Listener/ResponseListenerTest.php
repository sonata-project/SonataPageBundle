<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Listener;

use Sonata\PageBundle\Listener\ResponseListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\PageBundle\Model\PageInterface;

/**
 * Test the page bundle response listener
 */
class ResponseListenerTest extends \PHPUnit_Framework_TestCase
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
     * @var ResponseListener
     */
    protected $listener;

    /**
     * setup unit test
     */
    public function setUp()
    {
        $this->decoratorStrategy = $this->getMock('Sonata\PageBundle\CmsManager\DecoratorStrategyInterface');
        $this->pageServiceManager = $this->getMock('Sonata\PageBundle\Page\PageServiceManagerInterface');
        $this->cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $this->cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $this->cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($this->cmsManager));
        $this->templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');

        $this->listener = new ResponseListener($this->cmsSelector, $this->pageServiceManager, $this->decoratorStrategy, $this->templating);
    }

    /**
     * Test the listener without a page
     *
     * @expectedException \Sonata\PageBundle\Exception\InternalErrorException
     */
    public function testWithoutPage()
    {
        // GIVEN

        // mocked decorator strategy should accept to decorate
        $this->decoratorStrategy->expects($this->once())->method('isDecorable')->will($this->returnValue(true));

        // mocked cms manager should return the mock page
        $this->cmsManager->expects($this->once())->method('getCurrentPage')->will($this->returnValue(null));

        $event = $this->getMockEvent('content');

        // WHEN
        $this->listener->onCoreResponse($event);

        // THEN
        // exception thrown
    }

    /**
     * Test that the  listener does not mess up with response when a page is non decorable
     */
    public function testPageIsNonDecorable()
    {
        // GIVEN
        $this->decoratorStrategy->expects($this->once())->method('isDecorable')->will($this->returnValue(false));

        $event = $this->getMockEvent('content');

        // WHEN
        $this->listener->onCoreResponse($event);

        // THEN
        $this->assertEquals('content', $event->getResponse()->getContent(), 'response should not be altered when non-decorable');
    }

    /**
     * Test that the listener correctly decorates the response content when a page is decorable
     */
    public function testPageIsDecorable()
    {
        // GIVEN

        // a response content
        $content = 'inner';

        // a mock page entity that accepts to be decorated
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('isHybrid')->will($this->returnValue(true));
        $page->expects($this->once())->method('getDecorate')->will($this->returnValue(true));

        // mocked cms manager should return the mock page
        $this->cmsManager->expects($this->once())->method('getCurrentPage')->will($this->returnValue($page));

        // mocked decorator strategy should accept to decorate
        $this->decoratorStrategy->expects($this->once())->method('isDecorable')->will($this->returnValue(true));

        // a mock page service manager that decorates a response
        $this->pageServiceManager->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($page), $this->anything(), array('content' => $content))
            ->will($this->returnCallback(function(PageInterface $page, Request $request, array $params = array(), Response $response) {
                $response->setContent(sprintf('outer "%s" outer', $params['content']));

                return $response;
            }));

        // create a response event
        $event = $this->getMockEvent($content);

        // WHEN
        $this->listener->onCoreResponse($event);

        // THEN
        $this->assertEquals('outer "inner" outer', $event->getResponse()->getContent());
    }

    /**
     * Test that the listener correctly alters the http headers when the editor is enabled
     */
    public function testPageIsEditor()
    {
        // GIVEN
        $this->cmsSelector->expects($this->once())->method('isEditor')->will($this->returnValue(true));
        $event = $this->getMockEvent('inner');

        // WHEN
        $this->listener->onCoreResponse($event);

        // THEN
        $this->assertFalse($event->getResponse()->isCacheable(), 'Should not be cacheable in editor mode');

        // assert a cookie has been set in the response headers
        $cookies = $event->getResponse()->headers->getCookies();
        $foundCookie = false;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() == 'sonata_page_is_editor') {
                $this->assertEquals(1, $cookie->getValue());
                $foundCookie = true;
            }
        }

        $this->assertTrue($foundCookie, 'Should have found the editor mode cookie');
    }

    /**
     * Returns a mocked event with given content data
     *
     * @param string $content
     *
     * @return \Symfony\Component\HttpKernel\Event\FilterResponseEvent
     */
    protected function getMockEvent($content)
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();
        $response = new Response($content);

        return new FilterResponseEvent($kernel, $request, 'master', $response);
    }
}
