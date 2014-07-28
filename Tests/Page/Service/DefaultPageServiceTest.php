<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page\Service;

use Sonata\PageBundle\Page\Service\DefaultPageService;

/**
 * Test the default page service
 */
class DefaultPageServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultPageService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $seoPage;

    /**
     * setup unit tests
     */
    public function setUp()
    {
        $name = 'my name';
        $this->templateManager = $this->getMock('Sonata\PageBundle\Page\TemplateManagerInterface');
        $this->seoPage = $this->getMock('Sonata\SeoBundle\Seo\SeoPageInterface');

        $this->service = new DefaultPageService($name, $this->templateManager, $this->seoPage);
    }

    /**
     * Test the default page service execution
     */
    public function testExecute()
    {
        // GIVEN

        // mock a http request
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        // mock http response
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');

        // mock a page instance
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(2))->method('getTitle')->will($this->returnValue('page title'));
        $page->expects($this->atLeastOnce())->method('getMetaDescription')->will($this->returnValue('page meta description'));
        $page->expects($this->atLeastOnce())->method('getMetaKeyword')->will($this->returnValue('page meta keywords'));
        $page->expects($this->once())->method('getTemplateCode')->will($this->returnValue('template code'));

        // mocked SeoPage should expect SEO values
        $this->seoPage->expects($this->once())
            ->method('setTitle')->with($this->equalTo('page title'));

        $metaMapping = array(
            array('name',       'description',  'page meta description', true),
            array('name',       'keywords',     'page meta keywords',    true),
            array('property',   'og:type',      'article',               true)
        );

        $this->seoPage->expects($this->exactly(3))->method('addMeta')->will($this->returnValueMap($metaMapping));

        $this->seoPage->expects($this->once())
            ->method('addHtmlAttributes')->with($this->equalTo('prefix'), $this->equalTo('og: http://ogp.me/ns#'));

        // mocked template manager should render something
        $this->templateManager->expects($this->once())
            ->method('renderResponse')->with($this->equalTo('template code'))->will($this->returnValue($response));

        // WHEN
        $this->service->execute($page, $request);

        // THEN
        // mock asserts
    }
}
