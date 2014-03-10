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
        $page        = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $pageManager->expects($this->once())->method('findBy')->will($this->returnValue(array($page)));

        $paramFetcher = $this->getMock('FOS\RestBundle\Request\ParamFetcherInterface');
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue(array()));

        $this->assertEquals(array($page), $this->createPageController(null, $pageManager)->getPagesAction($paramFetcher));
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

    public function testGetPagePageblocksAction()
    {
        $page  = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $block = $this->getMock('Sonata\PageBundle\Model\PageBlockInterface');

        $page->expects($this->once())->method('getBlocks')->will($this->returnValue(array($block)));

        $this->assertEquals(array($block), $this->createPageController($page)->getPageBlocksAction(1));
    }

    /**
     * @param $page
     * @param $pageManager
     * @param $formFactory
     *
     * @return PageController
     */
    public function createPageController($page = null, $pageManager = null, $formFactory = null)
    {
        if (null === $pageManager) {
            $pageManager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        }
        if (null !== $page) {
            $pageManager->expects($this->once())->method('findOneBy')->will($this->returnValue($page));
        }
        if (null === $formFactory) {
            $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        }

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');

        return new PageController($pageManager, $formFactory, $backend);
    }
}
