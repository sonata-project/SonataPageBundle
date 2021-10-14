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

use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\PageBundle\Block\PageListBlockService;
use Sonata\PageBundle\Model\Page;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;

class PageListBlockServiceTest extends BlockServiceTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PageManagerInterface
     */
    protected $pageManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageManager = $this->createMock(PageManagerInterface::class);
    }

    public function testDefaultSettings(): void
    {
        $blockService = new PageListBlockService('block.service', $this->templating, $this->pageManager);
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

        $blockService = new PageListBlockService('block.service', $this->templating, $this->pageManager);
        $blockService->execute($blockContext);

        static::assertSame('@SonataPage/Block/block_pagelist.html.twig', $this->templating->view);

        static::assertSame($blockContext, $this->templating->parameters['context']);
        static::assertIsArray($this->templating->parameters['settings']);
        static::assertInstanceOf(BlockInterface::class, $this->templating->parameters['block']);
        static::assertCount(2, $this->templating->parameters['elements']);
        static::assertContains($page1, $this->templating->parameters['elements']);
        static::assertContains($page2, $this->templating->parameters['elements']);
        static::assertCount(1, $this->templating->parameters['systemElements']);
        static::assertContains($systemPage, $this->templating->parameters['systemElements']);
    }
}
