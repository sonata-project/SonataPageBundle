<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Route;

use Sonata\PageBundle\Route\CmsPageRouter;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\Routing\RequestContext;

/**
 *
 */
class CmsPageRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchToPageFound()
    {
        $cms = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');

        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->any())->method('retrieve')->will($this->returnValue($cms));

        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $cms = new CmsPageRouter($cmsSelector, $siteSelector);

        $cms->match('/');
    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchToCmsPage()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->any())->method('isCms')->will($this->returnValue(false));

        $cms = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cms->expects($this->any())->method('getPageByUrl')->will($this->returnValue($page));

        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->any())->method('retrieve')->will($this->returnValue($cms));

        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $cms = new CmsPageRouter($cmsSelector, $siteSelector);

        $cms->match('/');
    }

    public function testMatch()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->any())->method('isCms')->will($this->returnValue(true));

        $cms = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cms->expects($this->any())->method('getPageByUrl')->will($this->returnValue($page));
        $cms->expects($this->once())->method('setCurrentPage');

        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $cmsSelector->expects($this->any())->method('retrieve')->will($this->returnValue($cms));


        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $cms = new CmsPageRouter($cmsSelector, $siteSelector);

        $route = $cms->match('/');

        $this->assertEquals('sonata.page.renderer:render', $route['_controller']);
        $this->assertEquals('page_slug', $route['_route']);

    }

    /**
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateInvalidPage()
    {
        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');

        $cms = new CmsPageRouter($cmsSelector, $siteSelector);
        $cms->generate('foobar');
    }

    /**
     * @expectedException Sonata\NotificationBundle\Exception\InvalidParameterException
     */
    public function testGenerateInvalidParameterException()
    {
        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');

        $cms = new CmsPageRouter($cmsSelector, $siteSelector);
        $cms->generate('page_slug');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGenerateInvalidContext()
    {
        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');

        $cms = new CmsPageRouter($cmsSelector, $siteSelector);
        $cms->generate('page_slug');
    }

    public function testGenerateValidPageSlug()
    {
        $cmsSelector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $siteSelector = $this->getMock('Sonata\PageBundle\Site\SiteSelectorInterface');

        $cms = new CmsPageRouter($cmsSelector, $siteSelector);
        $cms->setContext(new RequestContext);
        $this->assertEquals('/my/path', $cms->generate('page_slug', array('path' => '/my/path')));
        $this->assertEquals('/my/path?foo=bar', $cms->generate('page_slug', array('path' => '/my/path', 'foo' => 'bar')));
        $this->assertEquals('http://localhost/my/path?foo=bar', $cms->generate('page_slug', array('path' => '/my/path', 'foo' => 'bar'), true));
    }
}