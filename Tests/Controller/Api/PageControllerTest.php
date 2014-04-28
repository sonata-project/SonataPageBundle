<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\Test\PageBundle\Controller\Api;

use Sonata\PageBundle\Controller\Api\PageController;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class PageControllerTest
 *
 * @package Sonata\Test\PageBundle\Controller\Api
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class PageControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPagesAction()
    {
        $pager = $this->getMockBuilder('Sonata\AdminBundle\Datagrid\Pager')->disableOriginalConstructor()->getMock();

        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->once())->method('getPager')->will($this->returnValue($pager));

        $paramFetcher = $this->getMock('FOS\RestBundle\Request\ParamFetcherInterface');
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue(array()));

        $this->assertSame($pager, $this->createPageController(null, null, $pageManager)->getPagesAction($paramFetcher));
    }

    public function testGetPageAction()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $this->assertEquals($page, $this->createPageController($page)->getPageAction(1));
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Page (42) not found
     */
    public function testGetPageActionNotFoundException()
    {
        $this->createPageController()->getPageAction(42);
    }

    public function testGetPageBlocksAction()
    {
        $page  = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $block = $this->getMock('Sonata\PageBundle\Model\PageBlockInterface');

        $page->expects($this->once())->method('getBlocks')->will($this->returnValue(array($block)));

        $this->assertEquals(array($block), $this->createPageController($page)->getPageBlocksAction(1));
    }

    public function testPostPageAction()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->once())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($page));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController(null, null, $pageManager, null, $formFactory)->postPageAction(new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPostPageInvalidAction()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->never())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController(null, null, $pageManager, null, $formFactory)->postPageAction(new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testPutPageAction()
    {
        $page = $this->getMock('Sonata\UserBundle\Model\PageInterface');

        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->once())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($page));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, null, $formFactory)->putPageAction(1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPutPageInvalidAction()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->never())->method('save')->will($this->returnValue($page));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, null, $formFactory)->putPageAction(1, new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testDeletePageAction()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->once())->method('delete');

        $view = $this->createPageController($page, null, $pageManager)->deletePageAction(1);

        $this->assertEquals(array('deleted' => true), $view);
    }

    public function testDeletePageInvalidAction()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->never())->method('delete');

        $this->createPageController(null, null, $pageManager)->deletePageAction(1);
    }

    public function testPostPageBlockAction()
    {
        $block = $this->getMock('Sonata\PageBundle\Model\Block');
        $block->expects($this->once())->method('setPage');

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $blockManager = $this->getMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->once())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($block));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, $blockManager, $formFactory)->postPageBlockAction(1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPostPageBlockInvalidAction()
    {
        $block = $this->getMock('Sonata\PageBundle\Model\Block');

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');

        $blockManager = $this->getMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        $blockManager->expects($this->never())->method('save')->will($this->returnValue($block));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createPageController($page, null, $pageManager, $blockManager, $formFactory)->postPageBlockAction(1, new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testPostPageSnapshotAction()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('createAndPublish');

        $view = $this->createPageController($page, null, null, null, null, $backend)->postPageSnapshotAction(1);

        $this->assertEquals(array('queued' => true), $view);
    }

    public function testPostPagesSnapshotsAction()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $siteManager->expects($this->once())->method('findAll')->will($this->returnValue(array($site)));

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('createAndPublish');

        $view = $this->createPageController(null, $siteManager, null, null, null, $backend)->postPagesSnapshotsAction();

        $this->assertEquals(array('queued' => true), $view);
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
            $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        }
        if (null === $pageManager) {
            $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        }
        if (null === $blockManager) {
            $blockManager = $this->getMock('Sonata\BlockBundle\Model\BlockManagerInterface');
        }
        if (null !== $page) {
            $pageManager->expects($this->once())->method('findOneBy')->will($this->returnValue($page));
        }
        if (null === $formFactory) {
            $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        }
        if (null === $backend) {
            $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        }

        return new PageController($siteManager, $pageManager, $blockManager, $formFactory, $backend);
    }
}
