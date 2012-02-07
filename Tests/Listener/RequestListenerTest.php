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

use Sonata\PageBundle\Listener\RequestListener;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Tests\Model\Site;

class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidSite()
    {
        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->once())->method('isRouteNameDecorable')->will($this->returnValue(true));
        $cmsManager->expects($this->once())->method('isRouteUriDecorable')->will($this->returnValue(true));

        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $templating = $this->getMock('Symfony\Component\Templating\EngineInterface');

        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->once())->method('retrieve')->will($this->returnValue($site));

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, 'master');

        $listener = new RequestListener($cmsSelector, $siteSelector, $templating);
        $listener->onCoreRequest($event);
    }

    public function testNoSite()
    {
        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->once())->method('isRouteNameDecorable')->will($this->returnValue(true));
        $cmsManager->expects($this->once())->method('isRouteUriDecorable')->will($this->returnValue(true));

        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $templating = $this->getMock('Symfony\Component\Templating\EngineInterface');
        $templating->expects($this->once())->method('render')->will($this->returnValue('Error'));

        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->once())->method('retrieve')->will($this->returnValue(false));

        $kernel = $this->getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $request = new Request();

        $event = new GetResponseEvent($kernel, $request, 'master');

        $listener = new RequestListener($cmsSelector, $siteSelector, $templating);
        $listener->onCoreRequest($event);

        $this->assertNotNull($event->getResponse());
        $this->assertEquals('Error', $event->getResponse()->getContent());
    }
}