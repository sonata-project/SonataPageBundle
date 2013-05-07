<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Block;

use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Model\Block;
use Sonata\PageBundle\Block\ContainerBlockService;

/**
 * Test Container Block service
 */
class ContainerBlockServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * test the block execute() method
     */
    public function testExecute()
    {
        $templating = new FakeTemplating();
        $service    = new ContainerBlockService('core.container', $templating);


        $block = new Block;
        $block->setName('block.name');
        $block->setType('core.container');
        $block->setSettings(array(
            'code' => 'block.code',
        ));

        $blockContext = new BlockContext($block, array(
            'code'        => '',
            'layout'      => '{{ CONTENT }}',
            'class'       => '',
            'template'    => 'SonataPageBundle:Block:block_container.html.twig',
        ));

        $service->execute($blockContext);

        $this->assertEquals('SonataPageBundle:Block:block_container.html.twig', $templating->view);
        $this->assertEquals('block.code', $templating->parameters['block']->getSetting('code'));
        $this->assertEquals('block.name', $templating->parameters['block']->getName());
        $this->assertInstanceOf('Sonata\BlockBundle\Model\Block', $templating->parameters['block']);
    }

    /**
     * test the container layout
     */
    public function testLayout()
    {
        $templating = new FakeTemplating();
        $service    = new ContainerBlockService('core.container', $templating);

        $block = new Block;
        $block->setName('block.name');
        $block->setType('core.container');

        // we manually perform the settings merge
        $blockContext = new BlockContext($block, array(
             'code'        => 'block.code',
             'layout'      => 'before{{ CONTENT }}after',
             'class'       => '',
             'template'    => 'SonataPageBundle:Block:block_container.html.twig',
         ));

        $service->execute($blockContext);

        $this->assertInternalType('array', $templating->parameters['decorator']);
        $this->assertArrayHasKey('pre', $templating->parameters['decorator']);
        $this->assertArrayHasKey('post', $templating->parameters['decorator']);
        $this->assertEquals('before', $templating->parameters['decorator']['pre']);
        $this->assertEquals('after', $templating->parameters['decorator']['post']);
    }

    /**
     * test the block's form builders
     */
    public function testFormBuilder()
    {
        $templating = new FakeTemplating();
        $service    = new ContainerBlockService('core.container', $templating);

        $block = new Block;
        $block->setName('block.name');
        $block->setType('core.container');
        $block->setSettings(array(
            'name' => 'block.code'
        ));

        $formMapper = $this->getMock('Sonata\\AdminBundle\\Form\\FormMapper', array(), array(), '', false);
        $formMapper->expects($this->exactly(6))->method('add');

        $service->buildCreateForm($formMapper, $block);
        $service->buildEditForm($formMapper, $block);
    }
}
