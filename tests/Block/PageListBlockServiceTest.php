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

namespace Sonata\PageBundle\Tests\Block;

use PHPUnit\Framework\MockObject\MockObject;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\PageBundle\Block\PageListBlockService;
use Sonata\PageBundle\Model\Page;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Symfony\Component\HttpFoundation\Response;

final class PageListBlockServiceTest extends BlockServiceTestCase
{
    /**
     * @var PageManagerInterface&MockObject
     */
    private $pageManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageManager = $this->createMock(PageManagerInterface::class);
    }

    public function testDefaultSettings(): void
    {
        $blockService = new PageListBlockService($this->twig, $this->pageManager);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'mode' => 'public',
            'title' => null,
            'translation_domain' => null,
            'icon' => 'fa fa-globe',
            'class' => null,
            'template' => '@SonataPage/Block/block_pagelist.html.twig',
        ], $blockContext);
    }

    public function testExecute(): void
    {
        $page1 = $this->createMock(PageInterface::class);
        $page2 = $this->createMock(PageInterface::class);
        $systemPage = $this->createMock(PageInterface::class);

        $this->twig->expects(static::once())
            ->method('render')
            ->with('@SonataPage/Block/block_pagelist.html.twig')
            ->willReturn('<p> {{ settings.title }} test </p>');

        $this->pageManager->expects(static::exactly(2))->method('findBy')->willReturnMap([
            [['routeName' => Page::PAGE_ROUTE_CMS_NAME], null, null, null, [$page1, $page2]],
            [['url' => null, 'parent' => null], null, null, null, [$systemPage]],
        ]);

        $block = new Block();

        $blockContext = new BlockContext($block, [
            'mode' => 'public',
            'title' => 'List Pages',
            'template' => '@SonataPage/Block/block_pagelist.html.twig',
        ]);

        $blockService = new PageListBlockService($this->twig, $this->pageManager);
        $response = $blockService->execute($blockContext);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('<p> {{ settings.title }} test </p>', $response->getContent());
        static::assertSame(200, $response->getStatusCode());
    }

    public function testFormBuilder(): void
    {
        $block = new Block();

        $form = $this->createMock(FormMapper::class);
        $form->expects(static::once())->method('add');

        $blockService = new PageListBlockService($this->twig, $this->pageManager);
        $blockService->configureEditForm($form, $block);
    }
}
