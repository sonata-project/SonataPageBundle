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

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\PageBundle\Block\ContainerBlockService;
use Symfony\Component\Form\Test\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;

final class ContainerBlockServiceTest extends BlockServiceTestCase
{
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

        $service = new ContainerBlockService($this->twig);
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

    public function testFormBuilder(): void
    {
        $block = new Block();
        $block->setName('block.name');
        $block->setType('core.container');
        $block->setSettings([
            'name' => 'block.code',
        ]);

        $service = new ContainerBlockService($this->twig);

        $formBuilder = $this->createMock(FormBuilderInterface::class);
        $form = new FormMapper(
            $this->createStub(FormContractorInterface::class),
            $formBuilder,
            $this->createStub(AdminInterface::class)
        );

        $formBuilder->expects(static::exactly(2))->method('add');

        $service->configureCreateForm($form, $block);
    }
}
