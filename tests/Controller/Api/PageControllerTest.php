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

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Datagrid\Pager;
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\Controller\Api\PageController;
use Sonata\PageBundle\Model\Block;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class PageControllerTest extends TestCase
{
    public function testGetPagesAction()
    {
        $pager = $this->getMockBuilder(Pager::class)->disableOriginalConstructor()->getMock();

        $paramFetcher = $this->getMockBuilder(ParamFetcherInterface::class)
            ->setMethods(['setController', 'get', 'all'])
            ->getMock();

        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue([]));

        $pageManager = $this->getMockBuilder(PageManagerInterface::class)->getMock();
        $pageManager->expects($this->once())->method('getPager')->will($this->returnValue($pager));

        $this->assertSame($pager, $this->createPageController(null, null, $pageManager)->getPagesAction($paramFetcher));
    }

    public function testGetPageAction()
    {
        $page = $this->createMock(PageInterface::class);

        $this->assertEquals($page, $this->createPageController($page)->getPageAction(1));
    }

    public function testGetPageActionNotFoundException()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Page (42) not found');

        $this->createPageController()->getPageAction(42);
    }

    public function testGetPageBlocksAction()
    {
        $page = $this->createMock(PageInterface::class);
        $block = $this->createMock(PageBlockInterface::class);

        $page->expects($this->once())->method('getBlocks')->will($this->returnValue([$block]));

        $this->assertEquals([$block], $this->createPageController($page)->getPageBlocksAction(1));
    }

    public function testPostPageAction()
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects($this->once())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($page));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController(null, null, $pageManager, null, $formFactory)->postPageAction(new Request());

        $this->assertInstanceOf(View::class, $view);
    }

    public function testPostPageInvalidAction()
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects($this->never())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController(null, null, $pageManager, null, $formFactory)->postPageAction(new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testPutPageAction()
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects($this->once())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($page));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, null, $formFactory)->putPageAction(1, new Request());

        $this->assertInstanceOf(View::class, $view);
    }

    public function testPutPageInvalidAction()
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects($this->never())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, null, $formFactory)->putPageAction(1, new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeletePageAction()
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects($this->once())->method('delete');

        $view = $this->createPageController($page, null, $pageManager)->deletePageAction(1);

        $this->assertEquals(['deleted' => true], $view);
    }

    public function testDeletePageInvalidAction()
    {
        $this->expectException(NotFoundHttpException::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects($this->never())->method('delete');

        $this->createPageController(null, null, $pageManager)->deletePageAction(1);
    }

    public function testPostPageBlockAction()
    {
        $block = $this->createMock(Block::class);
        $block->expects($this->once())->method('setPage');

        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->once())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($block));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $pageController = $this->createPageController($page, null, $pageManager, $blockManager, $formFactory);

        $block = $pageController->postPageBlockAction(1, new Request());

        $this->assertInstanceOf(Block::class, $block);
    }

    public function testPostPageBlockInvalidAction()
    {
        $block = $this->createMock(Block::class);

        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects($this->never())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, $blockManager, $formFactory)->postPageBlockAction(1, new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testPostPageSnapshotAction()
    {
        $page = $this->createMock(PageInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects($this->once())->method('createAndPublish');

        $view = $this->createPageController($page, null, null, null, null, $backend)->postPageSnapshotAction(1);

        $this->assertEquals(['queued' => true], $view);
    }

    public function testPostPagesSnapshotsAction()
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects($this->once())->method('findAll')->will($this->returnValue([$site]));

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects($this->once())->method('createAndPublish');

        $view = $this->createPageController(null, $siteManager, null, null, null, $backend)->postPagesSnapshotsAction();

        $this->assertEquals(['queued' => true], $view);
    }

    /**
     * @param $page
     * @param $siteManager
     * @param $pageManager
     * @param $blockManager
     * @param $formFactory
     * @param $backend
     *
     * @return PageController
     */
    public function createPageController($page = null, $siteManager = null, $pageManager = null, $blockManager = null, $formFactory = null, $backend = null)
    {
        if (null === $siteManager) {
            $siteManager = $this->createMock(SiteManagerInterface::class);
        }
        if (null === $pageManager) {
            $pageManager = $this->createMock(PageManagerInterface::class);
        }
        if (null === $blockManager) {
            $blockManager = $this->createMock(BlockManagerInterface::class);
        }
        if (null !== $page) {
            $pageManager->expects($this->once())->method('findOneBy')->will($this->returnValue($page));
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }
        if (null === $backend) {
            $backend = $this->createMock(BackendInterface::class);
        }

        return new PageController($siteManager, $pageManager, $blockManager, $formFactory, $backend);
    }
}
