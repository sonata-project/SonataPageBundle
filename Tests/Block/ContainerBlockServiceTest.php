<?php

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
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
use Sonata\PageBundle\Block\ContainerBlockService;

/**
 * Test Container Block service.
 */
class ContainerBlockServiceTest extends AbstractBlockServiceTestCase
{
    /**
     * test the block execute() method.
     */
    public function testExecute()
    {
        $service = new ContainerBlockService('core.container', $this->templating);

        $block = new Block();
        $block->setName('block.name');
        $block->setType('core.container');
        $block->setSettings(array(
            'code' => 'block.code',
        ));

        $blockContext = new BlockContext($block, array(
            'code' => '',
            'layout' => '{{ CONTENT }}',
            'class' => '',
            'template' => 'SonataPageBundle:Block:block_container.html.twig',
        ));

        $service->execute($blockContext);

        $this->assertEquals('SonataPageBundle:Block:block_container.html.twig', $this->templating->view);
        $this->assertEquals('block.code', $this->templating->parameters['block']->getSetting('code'));
        $this->assertEquals('block.name', $this->templating->parameters['block']->getName());
        $this->assertInstanceOf('Sonata\BlockBundle\Model\Block', $this->templating->parameters['block']);
    }

    /**
     * test the container layout.
     */
    public function testLayout()
    {
        $service = new ContainerBlockService('core.container', $this->templating);

        $block = new Block();
        $block->setName('block.name');
        $block->setType('core.container');

        // we manually perform the settings merge
        $blockContext = new BlockContext($block, array(
             'code' => 'block.code',
             'layout' => 'before{{ CONTENT }}after',
             'class' => '',
             'template' => 'SonataPageBundle:Block:block_container.html.twig',
         ));

        $service->execute($blockContext);

        $this->assertInternalType('array', $this->templating->parameters['decorator']);
        $this->assertArrayHasKey('pre', $this->templating->parameters['decorator']);
        $this->assertArrayHasKey('post', $this->templating->parameters['decorator']);
        $this->assertEquals('before', $this->templating->parameters['decorator']['pre']);
        $this->assertEquals('after', $this->templating->parameters['decorator']['post']);
    }

    /**
     * test the block's form builders.
     */
    public function testFormBuilder()
    {
        $service = new ContainerBlockService('core.container', $this->templating);

        $block = new Block();
        $block->setName('block.name');
        $block->setType('core.container');
        $block->setSettings(array(
            'name' => 'block.code',
        ));

        $formMapper = $this->getMockBuilder('Sonata\\AdminBundle\\Form\\FormMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $formMapper->expects($this->exactly(6))->method('add');

        $service->buildCreateForm($formMapper, $block);
        $service->buildEditForm($formMapper, $block);
    }
}
