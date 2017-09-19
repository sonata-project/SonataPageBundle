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

use Sonata\PageBundle\Controller\Api\BlockController;
use Sonata\PageBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class BlockControllerTest extends PHPUnit_Framework_TestCase
{
    public function testGetBlockAction()
    {
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $this->assertEquals($block, $this->createBlockController($block)->getBlockAction(1));
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Block (42) not found
     */
    public function testGetBlockActionNotFoundException()
    {
        $this->createBlockController()->getBlockAction(42);
    }

    public function testPutBlockAction()
    {
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $blockManager = $this->createMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->once())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($block));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createBlockController($block, $blockManager, $formFactory)->putBlockAction(1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPutBlockInvalidAction()
    {
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $blockManager = $this->createMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->never())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createBlockController($block, $blockManager, $formFactory)->putBlockAction(1, new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testDeleteBlockAction()
    {
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $blockManager = $this->createMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->once())->method('delete');

        $view = $this->createBlockController($block, $blockManager)->deleteBlockAction(1);

        $this->assertEquals(array('deleted' => true), $view);
    }

    public function testDeleteBlockInvalidAction()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $blockManager = $this->createMock('Sonata\BlockBundle\Model\BlockManagerInterface');
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
            $blockManager = $this->createMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        }
        if (null !== $block) {
            $blockManager->expects($this->once())->method('findOneBy')->will($this->returnValue($block));
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        }

        return new BlockController($blockManager, $formFactory);
    }
}
