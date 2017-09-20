<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Route;

use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Request\SiteRequestContext;
use Sonata\PageBundle\Route\CmsPageRouter;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Sonata\PageBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class CmsPageRouterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    /**
     * @var SiteSelectorInterface
     */
    protected $siteSelector;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $defaultRouter;

    /**
     * @var CmsPageRouter
     */
    protected $router;

    /**
     * Setup test object with its dependencies.
     */
    public function setup()
    {
        $this->cmsSelector = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $this->siteSelector = $this->createMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $this->defaultRouter = $this->getMockBuilder('Symfony\Component\Routing\Router')->disableOriginalConstructor()->getMock();

        $this->router = new CmsPageRouter($this->cmsSelector, $this->siteSelector, $this->defaultRouter);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchToPageFound()
    {
        $cms = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $this->cmsSelector->expects($this->any())->method('retrieve')->will($this->returnValue($cms));

        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');
        $this->siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $this->router->match('/');
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\ResourceNotFoundException
     */
    public function testMatchOnlyCmsPage()
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->any())->method('isCms')->will($this->returnValue(false));

        $cms = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cms->expects($this->any())->method('getPageByUrl')->will($this->returnValue($page));

        $this->cmsSelector->expects($this->any())->method('retrieve')->will($this->returnValue($cms));

        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');
        $this->siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $this->router->match('/');
    }

    public function testMatch()
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->any())->method('isCms')->will($this->returnValue(true));
        $page->expects($this->any())->method('getEnabled')->will($this->returnValue(true));

        $cms = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cms->expects($this->any())->method('getPageByUrl')->will($this->returnValue($page));
        $cms->expects($this->once())->method('setCurrentPage');

        $this->cmsSelector->expects($this->any())->method('retrieve')->will($this->returnValue($cms));

        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');
        $this->siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $route = $this->router->match('/');

        $this->assertEquals('sonata.page.page_service_manager:execute', $route['_controller']);
        $this->assertEquals('page_slug', $route['_route']);
    }

    /**
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function testGenerateInvalidPage()
    {
        $this->router->generate('foobar');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGenerateWithPageSlugInvalidParameterException()
    {
        $this->router->generate('page_slug', array());
    }

    public function testSupports()
    {
        $this->assertTrue($this->router->supports('page_slug'));
        $this->assertTrue($this->router->supports('_page_alias_homepage'));
        $this->assertFalse($this->router->supports('foobar'));
        $this->assertFalse($this->router->supports(new \stdClass()));
        $this->assertTrue($this->router->supports($this->createMock('Sonata\PageBundle\Model\PageInterface')));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGenerateWithPageSlugInvalidContext()
    {
        $this->router->generate('page_slug', array('path' => '/path/to/page'));
    }

    public function testGenerateWithPageSlugValid()
    {
        $this->router->setContext(new RequestContext());

        $url = $this->router->generate('page_slug', array('path' => '/my/path'));
        $this->assertEquals('/my/path', $url);

        $url = $this->router->generate('page_slug', array('path' => '/my/path', 'foo' => 'bar'));
        $this->assertEquals('/my/path?foo=bar', $url);

        $url = $this->router->generate('page_slug', array('path' => '/my/path', 'foo' => 'bar'), UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('/my/path?foo=bar', $url);

        $url = $this->router->generate('page_slug', array('path' => '/my/path', 'foo' => 'bar'), UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertEquals('my/path?foo=bar', $url);

        $url = $this->router->generate('page_slug', array('path' => '/my/path', 'foo' => 'bar'), UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertEquals('http://localhost/my/path?foo=bar', $url);

        $url = $this->router->generate('page_slug', array('path' => '/my/path', 'foo' => 'bar'), UrlGeneratorInterface::NETWORK_PATH);
        $this->assertEquals('//localhost/my/path?foo=bar', $url);
    }

    public function testGenerateWithPage()
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(false));
        $page->expects($this->exactly(5))->method('getUrl')->will($this->returnValue('/test/path'));

        $this->router->setContext(new RequestContext());

        $url = $this->router->generate($page, array('key' => 'value'));
        $this->assertEquals('/test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('/test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertEquals('test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertEquals('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::NETWORK_PATH);
        $this->assertEquals('//localhost/test/path?key=value', $url);
    }

    public function testGenerateWithPageCustomUrl()
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(false));
        $page->expects($this->exactly(5))->method('getCustomUrl')->will($this->returnValue('/test/path'));

        $this->router->setContext(new RequestContext());

        $url = $this->router->generate($page, array('key' => 'value'));
        $this->assertEquals('/test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('/test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertEquals('test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertEquals('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::NETWORK_PATH);
        $this->assertEquals('//localhost/test/path?key=value', $url);
    }

    public function testGenerateWithHybridPage()
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(true));
        $page->expects($this->exactly(5))->method('getRouteName')->will($this->returnValue('test_route'));

        $this->defaultRouter->expects($this->at(0))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH))
            ->will($this->returnValue('/test/path?key=value'));

        $this->defaultRouter->expects($this->at(1))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH))
            ->will($this->returnValue('/test/path?key=value'));

        $this->defaultRouter->expects($this->at(2))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::RELATIVE_PATH))
            ->will($this->returnValue('test/path?key=value'));

        $this->defaultRouter->expects($this->at(3))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::ABSOLUTE_URL))
            ->will($this->returnValue('http://localhost/test/path?key=value'));

        $this->defaultRouter->expects($this->at(4))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::NETWORK_PATH))
            ->will($this->returnValue('//localhost/test/path?key=value'));

        $url = $this->router->generate($page, array('key' => 'value'));
        $this->assertEquals('/test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('/test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertEquals('test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertEquals('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate($page, array('key' => 'value'), UrlGeneratorInterface::NETWORK_PATH);
        $this->assertEquals('//localhost/test/path?key=value', $url);
    }

    public function testGenerateWithPageAlias()
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(false));
        $page->expects($this->exactly(5))->method('getUrl')->will($this->returnValue('/test/path'));

        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');
        $this->siteSelector->expects($this->any())->method('retrieve')->will($this->returnValue($site));

        $cmsManager = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->exactly(5))->method('getPageByPageAlias')->will($this->returnValue($page));
        $this->cmsSelector->expects($this->exactly(5))->method('retrieve')->will($this->returnValue($cmsManager));

        $this->router->setContext(new RequestContext());

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'));
        $this->assertEquals('/test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('/test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'), UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertEquals('test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertEquals('http://localhost/test/path?key=value', $url);

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'), UrlGeneratorInterface::NETWORK_PATH);
        $this->assertEquals('//localhost/test/path?key=value', $url);
    }

    public function testGenerateWithPageAliasFromHybridPage()
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(5))->method('isHybrid')->will($this->returnValue(true));
        $page->expects($this->exactly(5))->method('getRouteName')->will($this->returnValue('test_route'));

        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');
        $this->siteSelector->expects($this->exactly(5))->method('retrieve')->will($this->returnValue($site));

        $cmsManager = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->exactly(5))->method('getPageByPageAlias')->will($this->returnValue($page));
        $this->cmsSelector->expects($this->exactly(5))->method('retrieve')->will($this->returnValue($cmsManager));

        $this->defaultRouter->expects($this->at(0))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH))
            ->will($this->returnValue('/test/key/value'));

        $this->defaultRouter->expects($this->at(1))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::ABSOLUTE_PATH))
            ->will($this->returnValue('/test/key/value'));

        $this->defaultRouter->expects($this->at(2))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::RELATIVE_PATH))
            ->will($this->returnValue('test/key/value'));

        $this->defaultRouter->expects($this->at(3))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::ABSOLUTE_URL))
            ->will($this->returnValue('http://localhost/test/key/value'));

        $this->defaultRouter->expects($this->at(4))
            ->method('generate')
            ->with($this->equalTo('test_route'), $this->equalTo(array('key' => 'value')), $this->equalTo(UrlGeneratorInterface::NETWORK_PATH))
            ->will($this->returnValue('//localhost/test/key/value'));

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'));
        $this->assertEquals('/test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('/test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'), UrlGeneratorInterface::RELATIVE_PATH);
        $this->assertEquals('test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'), UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertEquals('http://localhost/test/key/value', $url);

        $url = $this->router->generate('_page_alias_homepage', array('key' => 'value'), UrlGeneratorInterface::NETWORK_PATH);
        $this->assertEquals('//localhost/test/key/value', $url);
    }

    public function testGenerateWithPageAndNewSiteContext()
    {
        $site1 = $this->createMock('Sonata\PageBundle\Model\SiteInterface');
        $site1->expects($this->exactly(1))->method('getRelativePath')->will($this->returnValue('/site1'));
        $site2 = $this->createMock('Sonata\PageBundle\Model\SiteInterface');
        $site2->expects($this->exactly(1))->method('getRelativePath')->will($this->returnValue('/site2'));

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $page->expects($this->exactly(2))->method('isHybrid')->will($this->returnValue(false));
        $page->expects($this->exactly(2))->method('getUrl')->will($this->returnValue('/test/path'));

        $page2 = clone $page;
        $page2->expects($this->exactly(1))->method('getSite')->will($this->returnValue($site2));
        $page->expects($this->exactly(1))->method('getSite')->will($this->returnValue($site1));

        $siteSelector = $this->createMock('Sonata\PageBundle\Site\SiteSelectorInterface');
        $siteSelector->expects($this->exactly(1))->method('retrieve')->will($this->returnValue($site1));

        $this->router->setContext(new SiteRequestContext($siteSelector));

        $url = $this->router->generate($page, array('key' => 'value'));
        $this->assertEquals('/site1/test/path?key=value', $url);

        $url = $this->router->generate($page2, array('key' => 'value'));
        $this->assertEquals('/site2/test/path?key=value', $url);
    }
}
