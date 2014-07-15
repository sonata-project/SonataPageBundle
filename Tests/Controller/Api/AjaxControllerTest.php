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

use Sonata\PageBundle\Controller\AjaxController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BlockControllerTest
 *
 * @package Sonata\Test\PageBundle\Controller\Api
 *
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class AjaxControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Sonata\BlockBundle\Exception\BlockNotFoundException
     */
    public function testWithInvalidBlock()
    {
        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');

        $selector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $selector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $renderer = $this->getMock('Sonata\BlockBundle\Block\BlockRendererInterface');

        $contextManager = $this->getMock('\Sonata\BlockBundle\Block\BlockContextManagerInterface');

        $controller = new AjaxController($selector, $renderer, $contextManager);

        $request = new Request();

        $controller->execute($request, 10, 12);
    }

    public function testRenderer()
    {

        $block = $this->getMock('Sonata\BlockBundle\Model\BlockInterface');

        $cmsManager = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->once())->method('getBlock')->will($this->returnValue($block));

        $selector = $this->getMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $selector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $renderer = $this->getMock('Sonata\BlockBundle\Block\BlockRendererInterface');
        $renderer->expects($this->once())->method('render')->will($this->returnValue(new Response()));

        $blockContext = $this->getMock('Sonata\BlockBundle\Block\BlockContextInterface');

        $contextManager = $this->getMock('\Sonata\BlockBundle\Block\BlockContextManagerInterface');
        $contextManager->expects($this->once())->method('get')->will($this->returnValue($blockContext));

        $controller = new AjaxController($selector, $renderer, $contextManager);

        $request = new Request();

        $response = $controller->execute($request, 10, 12);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }
}