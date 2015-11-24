<?php

namespace Sonata\PageBundle\Tests\Block;

use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Tests\Block\AbstractBlockServiceTest;
use Sonata\BlockBundle\Tests\Block\Service\FakeTemplating;
use Sonata\PageBundle\Block\PageListBlockService;
use Sonata\PageBundle\Model\Page;
use Sonata\PageBundle\Model\PageManagerInterface;

class PageListBlockServiceTest extends AbstractBlockServiceTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PageManagerInterface
     */
    protected $pageManager;

    protected function setUp()
    {
        parent::setUp();

        $this->pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $this->templating      = new FakeTemplating();
    }

    public function testDefaultSettings()
    {
        $blockService = new PageListBlockService('block.service', $this->templating, $this->pageManager);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings(array(
            'mode'     => 'public',
            'title'    => 'List Pages',
            'template' => 'SonataPageBundle:Block:block_pagelist.html.twig',
        ), $blockContext);
    }

    public function testExecute()
    {
        $page1 = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page2 = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $systemPage = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $this->pageManager->expects($this->at(0))->method('findBy')
            ->with($this->equalTo(array(
                'routeName' => Page::PAGE_ROUTE_CMS_NAME,
            )))
            ->will($this->returnValue(array($page1, $page2)));
        $this->pageManager->expects($this->at(1))->method('findBy')
            ->with($this->equalTo(array(
                'url'    => null,
                'parent' => null,
            )))
            ->will($this->returnValue(array($systemPage)));

        $block = new Block();

        $blockContext = new BlockContext($block, array(
            'mode'     => 'public',
            'title'    => 'List Pages',
            'template' => 'SonataPageBundle:Block:block_pagelist.html.twig',
        ));

        $blockService = new PageListBlockService('block.service', $this->templating, $this->pageManager);
        $blockService->execute($blockContext);

        $this->assertSame('SonataPageBundle:Block:block_pagelist.html.twig', $this->templating->view);

        $this->assertSame($blockContext, $this->templating->parameters['context']);
        $this->assertInternalType('array', $this->templating->parameters['settings']);
        $this->assertInstanceOf('Sonata\BlockBundle\Model\BlockInterface', $this->templating->parameters['block']);
        $this->assertCount(2, $this->templating->parameters['elements']);
        $this->assertContains($page1, $this->templating->parameters['elements']);
        $this->assertContains($page2, $this->templating->parameters['elements']);
        $this->assertCount(1, $this->templating->parameters['systemElements']);
        $this->assertContains($systemPage, $this->templating->parameters['systemElements']);
    }
}
