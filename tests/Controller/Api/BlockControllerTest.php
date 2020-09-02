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
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class BlockControllerTest extends TestCase
{
    public function testGetBlockAction(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $this->assertSame($block, $this->createBlockController($block)->getBlockAction(1));
    }

    /**
     * @dataProvider getIdsForNotFound
     */
    public function testGetBlockActionNotFoundException($identifier, string $message): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage($message);

        $this->createBlockController()->getBlockAction($identifier);
    }

    /**
     * @phpstan-return list<array{mixed, string}>
     */
    public function getIdsForNotFound(): array
    {
        return [
            [42, 'Block not found for identifier 42.'],
            ['42', 'Block not found for identifier \'42\'.'],
            [null, 'Block not found for identifier NULL.'],
            ['', 'Block not found for identifier \'\'.'],
        ];
    }

    public function testPutBlockAction(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->once())->method('save')->willReturn($block);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($block);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $block = $this->createBlockController($block, $blockManager, $formFactory)->putBlockAction(1, new Request());

        $this->assertInstanceOf(BlockInterface::class, $block);
    }

    public function testPutBlockInvalidAction(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->never())->method('save')->willReturn($block);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $view = $this->createBlockController($block, $blockManager, $formFactory)->putBlockAction(1, new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeleteBlockAction(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->once())->method('delete');

        $view = $this->createBlockController($block, $blockManager)->deleteBlockAction(1);

        $this->assertSame(['deleted' => true], $view);
    }

    public function testDeleteBlockInvalidAction(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->never())->method('delete');

        $this->createBlockController(null, $blockManager)->deleteBlockAction(1);
    }

    public function createBlockController($block = null, $blockManager = null, $formFactory = null): BlockController
    {
        if (null === $blockManager) {
            $blockManager = $this->createMock(BlockManagerInterface::class);
        }
        if (null !== $block) {
            $blockManager->expects($this->once())->method('findOneBy')->willReturn($block);
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }

        return new BlockController($blockManager, $formFactory);
    }
}
