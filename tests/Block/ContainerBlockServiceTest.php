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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\PageBundle\Block\ContainerBlockService;

/**
 * Test Container Block service.
 */
final class ContainerBlockServiceTest extends BlockServiceTestCase
{
    /**
     * test the block execute() method.
     */
    public function testExecute(): void
    {
        $service = new ContainerBlockService($this->twig);

        $block = new Block();
        $block->setName('block.name');
        $block->setType('core.container');
        $block->setSettings([
            'code' => 'block.code',
        ]);

        $blockContext = new BlockContext($block, [
            'code' => '',
            'layout' => '{{ CONTENT }}',
            'class' => '',
            'template' => '@SonataPage/Block/block_container.html.twig',
        ]);

        $service->execute($blockContext);

        static::assertSame('@SonataPage/Block/block_container.html.twig', $this->twig->view);
        static::assertSame('block.code', $this->twig->parameters['block']->getSetting('code'));
        static::assertSame('block.name', $this->twig->parameters['block']->getName());
        static::assertInstanceOf(Block::class, $this->twig->parameters['block']);
    }

    /**
     * test the container layout.
     */
    public function testLayout(): void
    {
        $service = new ContainerBlockService($this->twig);

        $block = new Block();
        $block->setName('block.name');
        $block->setType('core.container');

        // we manually perform the settings merge
        $blockContext = new BlockContext($block, [
            'code' => 'block.code',
            'layout' => 'before{{ CONTENT }}after',
            'class' => '',
            'template' => '@SonataPage/Block/block_container.html.twig',
        ]);

        $service->execute($blockContext);
//        self::assertSettings([], $blockContext);
//
//        static::assertIsArray($this->twig->parameters['decorator']);
//        static::assertArrayHasKey('pre', $this->twig->parameters['decorator']);
//        static::assertArrayHasKey('post', $this->twig->parameters['decorator']);
//        static::assertSame('before', $this->twig->parameters['decorator']['pre']);
//        static::assertSame('after', $this->twig->parameters['decorator']['post']);
    }

    /**
     * test the block's form builders.
     */
    public function testFormBuilder(): void
    {
        $service = new ContainerBlockService($this->twig);

        $block = new Block();
        $block->setName('block.name');
        $block->setType('core.container');
        $block->setSettings([
            'name' => 'block.code',
        ]);

        $form = $this->createMock(FormMapper::class);
        $form->expects(static::exactly(6))->method('add');

        $service->buildCreateForm($form, $block);
        $service->buildEditForm($form, $block);
    }
}
