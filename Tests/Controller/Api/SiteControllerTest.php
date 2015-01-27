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

use Sonata\PageBundle\Controller\Api\SiteController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SiteControllerTest
 *
 * @package Sonata\Test\PageBundle\Controller\Api
 *
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 */
class SiteControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSitesAction()
    {
        $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $siteManager->expects($this->once())->method('getPager')->will($this->returnValue(array()));

        $paramFetcher = $this->getMock('FOS\RestBundle\Request\ParamFetcherInterface');
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue(array()));

        $this->assertEquals(array(), $this->createSiteController(null, $siteManager)->getSitesAction($paramFetcher));
    }

    public function testGetSiteAction()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $this->assertEquals($site, $this->createSiteController($site)->getSiteAction(1));
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Site (1) not found
     */
    public function testGetSiteActionNotFoundException()
    {
        $this->createSiteController()->getSiteAction(1);
    }

    public function testPostSiteAction()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $siteManager->expects($this->once())->method('save')->will($this->returnValue($site));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($site));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createSiteController(null, $siteManager, $formFactory)->postSiteAction(new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPostSiteInvalidAction()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $siteManager->expects($this->never())->method('save')->will($this->returnValue($site));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createSiteController(null, $siteManager, $formFactory)->postSiteAction(new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testPutSiteAction()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $siteManager->expects($this->once())->method('save')->will($this->returnValue($site));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($site));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createSiteController($site, $siteManager, $formFactory)->putSiteAction(1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
    }

    public function testPutSiteInvalidAction()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $siteManager->expects($this->never())->method('save')->will($this->returnValue($site));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $view = $this->createSiteController($site, $siteManager, $formFactory)->putSiteAction(1, new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testDeleteSiteAction()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $siteManager->expects($this->once())->method('delete');

        $view = $this->createSiteController($site, $siteManager)->deleteSiteAction(1);

        $this->assertEquals(array('deleted' => true), $view);
    }

    public function testDeleteSiteInvalidAction()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        $siteManager->expects($this->never())->method('delete');

        $this->createSiteController(null, $siteManager)->deleteSiteAction(1);
    }

    /**
     * @param $site
     * @param $siteManager
     * @param $formFactory
     *
     * @return SiteController
     */
    public function createSiteController($site = null, $siteManager = null, $formFactory = null)
    {
        if (null === $siteManager) {
            $siteManager = $this->getMock('Sonata\PageBundle\Model\SiteManagerInterface');
        }
        if (null !== $site) {
            $siteManager->expects($this->once())->method('findOneBy')->will($this->returnValue($site));
        }
        if (null === $formFactory) {
            $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        }

        return new SiteController($siteManager, $formFactory);
    }
}
