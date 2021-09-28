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
use Sonata\PageBundle\Controller\Api\SiteController;
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
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 *
 * @group legacy
 */
class SiteControllerTest extends TestCase
{
    public function testGetSitesAction(): void
    {
        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects(static::once())->method('getPager')->willReturn([]);

        $paramFetcher = $this->getMockBuilder(ParamFetcherInterface::class)
            ->setMethods(['setController', 'get', 'all'])
            ->getMock();

        $paramFetcher->expects(static::exactly(3))->method('get');
        $paramFetcher->expects(static::once())->method('all')->willReturn([]);

        static::assertSame([], $this->createSiteController(null, $siteManager)->getSitesAction($paramFetcher));
    }

    public function testGetSiteAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        static::assertSame($site, $this->createSiteController($site)->getSiteAction(1));
    }

    public function testGetSiteActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Site (1) not found');

        $this->createSiteController()->getSiteAction(1);
    }

    public function testPostSiteAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects(static::once())->method('save')->willReturn($site);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(true);
        $form->expects(static::once())->method('getData')->willReturn($site);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createSiteController(null, $siteManager, $formFactory)->postSiteAction(new Request());

        static::assertInstanceOf(View::class, $view);
    }

    public function testPostSiteInvalidAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects(static::never())->method('save')->willReturn($site);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createSiteController(null, $siteManager, $formFactory)->postSiteAction(new Request());

        static::assertInstanceOf(FormInterface::class, $view);
    }

    public function testPutSiteAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects(static::once())->method('save')->willReturn($site);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(true);
        $form->expects(static::once())->method('getData')->willReturn($site);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createSiteController($site, $siteManager, $formFactory)->putSiteAction(1, new Request());

        static::assertInstanceOf(View::class, $view);
    }

    public function testPutSiteInvalidAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects(static::never())->method('save')->willReturn($site);

        $form = $this->createMock(Form::class);
        $form->expects(static::once())->method('handleRequest');
        $form->expects(static::once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects(static::once())->method('createNamed')->willReturn($form);

        $view = $this->createSiteController($site, $siteManager, $formFactory)->putSiteAction(1, new Request());

        static::assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeleteSiteAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects(static::once())->method('delete');

        $view = $this->createSiteController($site, $siteManager)->deleteSiteAction(1);

        static::assertSame(['deleted' => true], $view);
    }

    public function testDeleteSiteInvalidAction(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects(static::never())->method('delete');

        $this->createSiteController(null, $siteManager)->deleteSiteAction(1);
    }

    public function createSiteController($site = null, $siteManager = null, $formFactory = null): SiteController
    {
        if (null === $siteManager) {
            $siteManager = $this->createMock(SiteManagerInterface::class);
        }
        if (null !== $site) {
            $siteManager->expects(static::once())->method('findOneBy')->willReturn($site);
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }

        return new SiteController($siteManager, $formFactory);
    }
}
