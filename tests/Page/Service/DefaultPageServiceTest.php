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

namespace Sonata\PageBundle\Tests\Page\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\Service\DefaultPageService;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DefaultPageServiceTest extends TestCase
{
    /**
     * @var MockObject&TemplateManagerInterface
     */
    private TemplateManagerInterface $templateManager;

    /**
     * @var MockObject&SeoPageInterface
     */
    private SeoPageInterface $seoPage;

    private DefaultPageService $service;

    protected function setUp(): void
    {
        $name = 'my name';
        $this->templateManager = $this->createMock(TemplateManagerInterface::class);
        $this->seoPage = $this->createMock(SeoPageInterface::class);

        $this->service = new DefaultPageService($name, $this->templateManager, $this->seoPage);
    }

    public function testExecute(): void
    {
        // mock a http request
        $request = $this->createMock(Request::class);

        // mock http response
        $response = $this->createMock(Response::class);

        // mock a page instance
        $page = $this->createMock(PageInterface::class);
        $page->method('getTitle')->willReturn('page title');
        $page->expects(static::atLeastOnce())->method('getMetaDescription')->willReturn('page meta description');
        $page->expects(static::atLeastOnce())->method('getMetaKeyword')->willReturn('page meta keywords');
        $page->expects(static::once())->method('getTemplateCode')->willReturn('template code');

        // mocked SeoPage should expect SEO values
        $this->seoPage->expects(static::once())
            ->method('setTitle')->with(static::equalTo('page title'));

        $this->seoPage->expects(static::exactly(3))
            ->method('addMeta')->willReturn($this->seoPage);

        $this->seoPage->expects(static::once())
            ->method('addHtmlAttributes')->with(static::equalTo('prefix'), static::equalTo('og: http://ogp.me/ns#'));

        // mocked template manager should render something
        $this->templateManager->expects(static::once())
            ->method('renderResponse')->with(static::equalTo('template code'))->willReturn($response);

        $this->service->execute($page, $request);
    }
}
