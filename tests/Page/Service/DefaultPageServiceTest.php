<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page\Service;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\DefaultPageService;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test the default page service.
 */
class DefaultPageServiceTest extends TestCase
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
     * setup unit tests.
     */
    public function setUp()
    {
        $name = 'my name';
        $this->templateManager = $this->createMock(TemplateManagerInterface::class);
        $this->seoPage = $this->createMock(SeoPageInterface::class);

        $this->service = new DefaultPageService($name, $this->templateManager, $this->seoPage);
    }

    /**
     * Test the default page service execution.
     */
    public function testExecute()
    {
        // GIVEN

        // mock a http request
        $request = $this->createMock(Request::class);

        // mock http response
        $response = $this->createMock(Response::class);

        // mock a page instance
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->any())->method('getTitle')->will($this->returnValue('page title'));
        $page->expects($this->atLeastOnce())->method('getMetaDescription')->will($this->returnValue('page meta description'));
        $page->expects($this->atLeastOnce())->method('getMetaKeyword')->will($this->returnValue('page meta keywords'));
        $page->expects($this->once())->method('getTemplateCode')->will($this->returnValue('template code'));

        // mocked SeoPage should expect SEO values
        $this->seoPage->expects($this->once())
            ->method('setTitle')->with($this->equalTo('page title'));

        $metaMapping = [
            ['name',       'description',  'page meta description', true],
            ['name',       'keywords',     'page meta keywords',    true],
            ['property',   'og:type',      'article',               true],
        ];

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
