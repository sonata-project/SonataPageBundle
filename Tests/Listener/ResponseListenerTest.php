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
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Tests\Model\Site;
use Sonata\PageBundle\CmsManager\PageRendererInterface;

class ResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testPageIsNonDecorable()
    {
        $pageRenderer = $this->getMock('Sonata\PageBundle\CmsManager\PageRendererInterface');

        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');

        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();
        $response = new Response('content');

        $event = new FilterResponseEvent($kernel, $request, 'master', $response);

        $listener = new ResponseListener($cmsSelector, $pageRenderer);
        $listener->onCoreResponse($event);

        $this->assertEquals('content', $event->getResponse()->getContent());
    }

    public function testPageIsDecorable()
    {
        $pageRenderer = $this->getMock('Sonata\PageBundle\CmsManager\PageRendererInterface');
        $pageRenderer->expects($this->once())->method('render')->will($this->returnCallback(function(PageInterface $page, array $params = array(), Response $r = null) use (&$response) {
            $response->setContent(sprintf('outter <%s> outter', $params['content']));
        }));

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('isHybrid')->will($this->returnValue(true));
        $page->expects($this->once())->method('getDecorate')->will($this->returnValue(true));

        // solve the reference issue with PHPUnit and closure
        $response = new Response('inner content');

        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->once())->method('getCurrentPage')->will($this->returnValue($page));
        $cmsManager->expects($this->once())->method('isDecorable')->will($this->returnValue(true));


        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new FilterResponseEvent($kernel, $request, 'master', $response);

        $listener = new ResponseListener($cmsSelector, $pageRenderer);
        $listener->onCoreResponse($event);

        $this->assertEquals('outter <inner content> outter', $event->getResponse()->getContent());
    }

    public function testResponseNotDecorable()
    {
        $pageRenderer = $this->getMock('Sonata\PageBundle\CmsManager\PageRendererInterface');

        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->once())->method('isDecorable')->will($this->returnValue(false));

        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));


        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();
        $response = new Response('inner content');
        $event = new FilterResponseEvent($kernel, $request, 'master', $response);

        $listener = new ResponseListener($cmsSelector, $pageRenderer);
        $listener->onCoreResponse($event);

        $this->assertEquals('inner content', $event->getResponse()->getContent());
    }
}