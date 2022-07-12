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
use Sonata\BlockBundle\Block\Service\ContainerBlockService as BaseContainerBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\PageBundle\Block\ContainerBlockService;
use Symfony\Component\HttpFoundation\Response;

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
        $block = new Block();
        $block->setName('block.name');
        $block->setType('core.container');
        $block->setSettings([
            'code' => 'block.code',
        ]);

        $this->twig->expects(static::once())
            ->method('render')
            ->with('@SonataPage/Block/block_container.html.twig')
            ->willReturn('<p> {{ settings.title }} test </p>');

        $baseContainerBlockService = new BaseContainerBlockService($this->twig);
        $service = new ContainerBlockService($baseContainerBlockService);
        $blockContext = new BlockContext($block, [
            'code' => '',
            'layout' => '{{ CONTENT }}',
            'class' => '',
            'template' => '@SonataPage/Block/block_container.html.twig',
        ]);

        $response = $service->execute($blockContext);

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('<p> {{ settings.title }} test </p>', $response->getContent());
        static::assertSame(200, $response->getStatusCode());
    }

    /**
     * test the block's form builders.
     */
    public function testFormBuilder(): void
    {
        $block = new Block();
        $block->setName('block.name');
        $block->setType('core.container');
        $block->setSettings([
            'name' => 'block.code',
        ]);

        $baseContainerBlockService = new BaseContainerBlockService($this->twig);
        $service = new ContainerBlockService($baseContainerBlockService);

        $form = $this->createMock(FormMapper::class);
        $form->expects(static::exactly(3))->method('add');

        $service->configureCreateForm($form, $block);
    }
}
