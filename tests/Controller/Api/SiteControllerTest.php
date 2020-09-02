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
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 */
class SiteControllerTest extends TestCase
{
    public function testGetSitesAction(): void
    {
        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects($this->once())->method('getPager')->willReturn([]);

        $paramFetcher = $this->getMockBuilder(ParamFetcherInterface::class)
            ->setMethods(['setController', 'get', 'all'])
            ->getMock();

        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->willReturn([]);

        $this->assertSame([], $this->createSiteController(null, $siteManager)->getSitesAction($paramFetcher));
    }

    public function testGetSiteAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $this->assertSame($site, $this->createSiteController($site)->getSiteAction(1));
    }

    /**
     * @dataProvider getIdsForNotFound
     */
    public function testGetSiteActionNotFoundException($identifier, string $message): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage($message);

        $this->createSiteController()->getSiteAction($identifier);
    }

    /**
     * @phpstan-return list<array{mixed, string}>
     */
    public function getIdsForNotFound(): array
    {
        return [
            [42, 'Site not found for identifier 42.'],
            ['42', 'Site not found for identifier \'42\'.'],
            [null, 'Site not found for identifier NULL.'],
            ['', 'Site not found for identifier \'\'.'],
        ];
    }

    public function testPostSiteAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects($this->once())->method('save')->willReturn($site);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($site);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $view = $this->createSiteController(null, $siteManager, $formFactory)->postSiteAction(new Request());

        $this->assertInstanceOf(View::class, $view);
    }

    public function testPostSiteInvalidAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects($this->never())->method('save')->willReturn($site);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $view = $this->createSiteController(null, $siteManager, $formFactory)->postSiteAction(new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testPutSiteAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects($this->once())->method('save')->willReturn($site);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($site);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $view = $this->createSiteController($site, $siteManager, $formFactory)->putSiteAction(1, new Request());

        $this->assertInstanceOf(View::class, $view);
    }

    public function testPutSiteInvalidAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects($this->never())->method('save')->willReturn($site);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $view = $this->createSiteController($site, $siteManager, $formFactory)->putSiteAction(1, new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeleteSiteAction(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects($this->once())->method('delete');

        $view = $this->createSiteController($site, $siteManager)->deleteSiteAction(1);

        $this->assertSame(['deleted' => true], $view);
    }

    public function testDeleteSiteInvalidAction(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->expects($this->never())->method('delete');

        $this->createSiteController(null, $siteManager)->deleteSiteAction(1);
    }

    public function createSiteController($site = null, $siteManager = null, $formFactory = null): SiteController
    {
        if (null === $siteManager) {
            $siteManager = $this->createMock(SiteManagerInterface::class);
        }
        if (null !== $site) {
            $siteManager->expects($this->once())->method('findOneBy')->willReturn($site);
        }
        if (null === $formFactory) {
            $formFactory = $this->createMock(FormFactoryInterface::class);
        }

        return new SiteController($siteManager, $formFactory);
    }
}
