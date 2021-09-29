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

namespace Sonata\PageBundle\Tests\Controller\Api;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Controller\Api\BlockController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 *
 * @group legacy
 */
class BlockControllerTest extends TestCase
{
    public function testGetBlockAction(): void
    {
        $block = $this->createMock(BlockInterface::class);

        static::assertSame($block, $this->createBlockController($block)->getBlockAction(1));
    }

    public function testGetBlockActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Block (42) not found');

        $this->createBlockController()->getBlockAction(42);
    }

    public function testPutBlockAction(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects(static::once())->method('save')->willReturn($block);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(true);
        $form->expects(static::once())->method('getData')->willReturn($block);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $block = $this->createBlockController($block, $blockManager, $formFactory)->putBlockAction(1, new Request());

        static::assertInstanceOf(BlockInterface::class, $block);
    }

    public function testPutBlockInvalidAction(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects(static::never())->method('save')->willReturn($block);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createBlockController($block, $blockManager, $formFactory)->putBlockAction(1, new Request());

        static::assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeleteBlockAction(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects(static::once())->method('delete');

        $view = $this->createBlockController($block, $blockManager)->deleteBlockAction(1);

        static::assertSame(['deleted' => true], $view);
    }

    public function testDeleteBlockInvalidAction(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects(static::never())->method('delete');

        $this->createBlockController(null, $blockManager)->deleteBlockAction(1);
    }

    public function createBlockController($block = null, $blockManager = null, $formFactory = null): BlockController
    {
        if (null === $blockManager) {
            $blockManager = $this->createMock(BlockManagerInterface::class);
        }
        if (null !== $block) {
            $blockManager->expects(static::once())->method('findOneBy')->willReturn($block);
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }

        return new BlockController($blockManager, $formFactory);
    }
}
