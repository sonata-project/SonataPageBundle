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
use Sonata\PageBundle\Controller\Api\PageController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class PageControllerTest extends TestCase
{
    public function testGetPagesAction(): void
    {
        $pager = $this->getMockBuilder('Sonata\AdminBundle\Datagrid\Pager')->disableOriginalConstructor()->getMock();

        $paramFetcher = $this->getMockBuilder('FOS\RestBundle\Request\ParamFetcherInterface')
            ->setMethods(['addParam', 'setController', 'get', 'all'])
            ->getMock();

        $paramFetcher->expects($this->once())->method('addParam');
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue([]));

        $pageManager = $this->getMockBuilder('Sonata\PageBundle\Model\PageManagerInterface')->getMock();
        $pageManager->expects($this->once())->method('getPager')->will($this->returnValue($pager));

        $this->assertSame($pager, $this->createPageController(null, null, $pageManager)->getPagesAction($paramFetcher));
    }

    public function testGetPageAction(): void
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $this->assertEquals($page, $this->createPageController($page)->getPageAction(1));
    }

    public function testGetPageActionNotFoundException(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('Page (42) not found');

        $this->createPageController()->getPageAction(42);
    }

    public function testGetPageBlocksAction(): void
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $block = $this->createMock('Sonata\PageBundle\Model\PageBlockInterface');

        $page->expects($this->once())->method('getBlocks')->will($this->returnValue([$block]));

        $this->assertEquals([$block], $this->createPageController($page)->getPageBlocksAction(1));
    }

    public function testPostPageAction(): void
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->once())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($page));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController(null, null, $pageManager, null, $formFactory)->postPageAction(new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPostPageInvalidAction(): void
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->never())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController(null, null, $pageManager, null, $formFactory)->postPageAction(new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testPutPageAction(): void
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->once())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($page));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, null, $formFactory)->putPageAction(1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPutPageInvalidAction(): void
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->never())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, null, $formFactory)->putPageAction(1, new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testDeletePageAction(): void
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->once())->method('delete');

        $view = $this->createPageController($page, null, $pageManager)->deletePageAction(1);

        $this->assertEquals(['deleted' => true], $view);
    }

    public function testDeletePageInvalidAction(): void
    {
        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->never())->method('delete');

        $this->createPageController(null, null, $pageManager)->deletePageAction(1);
    }

    public function testPostPageBlockAction(): void
    {
        $block = $this->createMock('Sonata\PageBundle\Model\Block');
        $block->expects($this->once())->method('setPage');

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $blockManager = $this->createMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->once())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($block));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, $blockManager, $formFactory)->postPageBlockAction(1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPostPageBlockInvalidAction(): void
    {
        $block = $this->createMock('Sonata\PageBundle\Model\Block');

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $blockManager = $this->createMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->never())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('submit');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, $blockManager, $formFactory)->postPageBlockAction(1, new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testPostPageSnapshotAction(): void
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');

        $backend = $this->createMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('createAndPublish');

        $view = $this->createPageController($page, null, null, null, null, $backend)->postPageSnapshotAction(1);

        $this->assertEquals(['queued' => true], $view);
    }

    public function testPostPagesSnapshotsAction(): void
    {
        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');

        $siteManager = $this->createMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $siteManager->expects($this->once())->method('findAll')->will($this->returnValue([$site]));

        $backend = $this->createMock('Sonata\NotificationBundle\Backend\BackendInterface');
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
            $siteManager = $this->createMock('Sonata\PageBundle\Model\SiteManagerInterface');
        }
        if (null === $pageManager) {
            $pageManager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        }
        if (null === $blockManager) {
            $blockManager = $this->createMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        }
        if (null !== $page) {
            $pageManager->expects($this->once())->method('findOneBy')->will($this->returnValue($page));
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        }
        if (null === $backend) {
            $backend = $this->createMock('Sonata\NotificationBundle\Backend\BackendInterface');
        }

        return new PageController($siteManager, $pageManager, $blockManager, $formFactory, $backend);
    }
}
