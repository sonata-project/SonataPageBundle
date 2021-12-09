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
 * NEXT_MAJOR: Remove this class.
 *
 * @author Hugo Briand <briand@ekino.com>
 *
 * @group legacy
 */
final class PageControllerTest extends TestCase
{
    public function testGetPagesAction(): void
    {
        $pager = $this->createMock(Pager::class);

        $paramFetcher = $this->getMockBuilder(ParamFetcherInterface::class)
            ->setMethods(['setController', 'get', 'all'])
            ->getMock();

        $paramFetcher->expects(static::exactly(3))->method('get');
        $paramFetcher->expects(static::once())->method('all')->willReturn([]);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects(static::once())->method('getPager')->willReturn($pager);

        static::assertSame($pager, $this->createPageController(null, null, $pageManager)->getPagesAction($paramFetcher));
    }

    public function testGetPageAction(): void
    {
        $page = $this->createMock(PageInterface::class);

        static::assertSame($page, $this->createPageController($page)->getPageAction(1));
    }

    public function testGetPageActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Page (42) not found');

        $this->createPageController()->getPageAction(42);
    }

    public function testGetPageBlocksAction(): void
    {
        $page = $this->createMock(PageInterface::class);
        $block = $this->createMock(PageBlockInterface::class);

        $page->expects(static::once())->method('getBlocks')->willReturn([$block]);

        static::assertSame([$block], $this->createPageController($page)->getPageBlocksAction(1));
    }

    public function testPostPageAction(): void
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects(static::once())->method('save')->willReturn($page);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(true);
        $form->expects(static::once())->method('getData')->willReturn($page);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createPageController(null, null, $pageManager, null, $formFactory)
            ->postPageAction(new Request());

        static::assertInstanceOf(View::class, $view);
    }

    public function testPostPageInvalidAction(): void
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects(static::never())->method('save')->willReturn($page);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createPageController(null, null, $pageManager, null, $formFactory)
            ->postPageAction(new Request());

        static::assertInstanceOf(FormInterface::class, $view);
    }

    public function testPutPageAction(): void
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects(static::once())->method('save')->willReturn($page);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(true);
        $form->expects(static::once())->method('getData')->willReturn($page);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createPageController($page, null, $pageManager, null, $formFactory)
            ->putPageAction(1, new Request());

        static::assertInstanceOf(View::class, $view);
    }

    public function testPutPageInvalidAction(): void
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects(static::never())->method('save')->willReturn($page);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createPageController($page, null, $pageManager, null, $formFactory)
            ->putPageAction(1, new Request());

        static::assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeletePageAction(): void
    {
        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects(static::once())->method('delete');

        $view = $this->createPageController($page, null, $pageManager)->deletePageAction(1);

        static::assertSame(['deleted' => true], $view);
    }

    public function testDeletePageInvalidAction(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager->expects(static::never())->method('delete');

        $this->createPageController(null, null, $pageManager)->deletePageAction(1);
    }

    public function testPostPageBlockAction(): void
    {
        $block = $this->createMock(Block::class);
        $block->expects(static::once())->method('setPage');

        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects(static::once())->method('save')->willReturn($block);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(true);
        $form->expects(static::once())->method('getData')->willReturn($block);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $pageController = $this->createPageController($page, null, $pageManager, $blockManager, $formFactory);

        $block = $pageController->postPageBlockAction(1, new Request());

        static::assertInstanceOf(Block::class, $block);
    }

    public function testPostPageBlockInvalidAction(): void
    {
        $block = $this->createMock(Block::class);

        $page = $this->createMock(PageInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->expects(static::never())->method('save')->willReturn($block);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createPageController($page, null, $pageManager, $blockManager, $formFactory)
            ->postPageBlockAction(1, new Request());

        static::assertInstanceOf(FormInterface::class, $view);
    }

    public function testPostPageSnapshotAction(): void
    {
        $page = $this->createMock(PageInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects(static::once())->method('createAndPublish');

        $view = $this->createPageController($page, null, null, null, null, $backend)
            ->postPageSnapshotAction(1);

        static::assertSame(['queued' => true], $view);
    }

    public function testPostPagesSnapshotsAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects(static::once())->method('findAll')->willReturn([$site]);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects(static::once())->method('createAndPublish');

        $view = $this->createPageController(null, $siteManager, null, null, null, $backend)
            ->postPagesSnapshotsAction();

        static::assertSame(['queued' => true], $view);
    }

    public function createPageController(
        $page = null,
        $siteManager = null,
        $pageManager = null,
        $blockManager = null,
        $formFactory = null,
        $backend = null
    ): PageController {
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
            $pageManager->expects(static::once())->method('findOneBy')->willReturn($page);
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
