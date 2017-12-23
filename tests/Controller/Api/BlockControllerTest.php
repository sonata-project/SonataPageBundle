<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Controller\Api;

use FOS\RestBundle\View\View;
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
    public function testGetBlockAction()
    {
        $block = $this->createMock(BlockInterface::class);

        $this->assertEquals($block, $this->createBlockController($block)->getBlockAction(1));
    }

    public function testGetBlockActionNotFoundException()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Block (42) not found');

        $this->createBlockController()->getBlockAction(42);
    }

    public function testPutBlockAction()
    {
        $block = $this->createMock(BlockInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->once())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($block));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createBlockController($block, $blockManager, $formFactory)->putBlockAction(1, new Request());

        $this->assertInstanceOf(View::class, $view);
    }

    public function testPutBlockInvalidAction()
    {
        $block = $this->createMock(BlockInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->never())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createBlockController($block, $blockManager, $formFactory)->putBlockAction(1, new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeleteBlockAction()
    {
        $block = $this->createMock(BlockInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->once())->method('delete');

        $view = $this->createBlockController($block, $blockManager)->deleteBlockAction(1);

        $this->assertEquals(['deleted' => true], $view);
    }

    public function testDeleteBlockInvalidAction()
    {
        $this->expectException(NotFoundHttpException::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->never())->method('delete');

        $this->createBlockController(null, $blockManager)->deleteBlockAction(1);
    }

    /**
     * @param $block
     * @param $blockManager
     * @param $formFactory
     *
     * @return BlockController
     */
    public function createBlockController($block = null, $blockManager = null, $formFactory = null)
    {
        if (null === $blockManager) {
            $blockManager = $this->createMock(BlockManagerInterface::class);
        }
        if (null !== $block) {
            $blockManager->expects($this->once())->method('findOneBy')->will($this->returnValue($block));
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }

        return new BlockController($blockManager, $formFactory);
    }
}
