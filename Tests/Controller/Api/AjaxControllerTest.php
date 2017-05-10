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

use Sonata\PageBundle\Controller\AjaxController;
use Sonata\PageBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class AjaxControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Sonata\BlockBundle\Exception\BlockNotFoundException
     */
    public function testWithInvalidBlock()
    {
        $cmsManager = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');

        $selector = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $selector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $renderer = $this->createMock('Sonata\BlockBundle\Block\BlockRendererInterface');

        $contextManager = $this->createMock('\Sonata\BlockBundle\Block\BlockContextManagerInterface');

        $controller = new AjaxController($selector, $renderer, $contextManager);

        $request = new Request();

        $controller->execute($request, 10, 12);
    }

    public function testRenderer()
    {
        $block = $this->createMock('Sonata\BlockBundle\Model\BlockInterface');

        $cmsManager = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerInterface');
        $cmsManager->expects($this->once())->method('getBlock')->will($this->returnValue($block));

        $selector = $this->createMock('Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface');
        $selector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $renderer = $this->createMock('Sonata\BlockBundle\Block\BlockRendererInterface');
        $renderer->expects($this->once())->method('render')->will($this->returnValue(new Response()));

        $blockContext = $this->createMock('Sonata\BlockBundle\Block\BlockContextInterface');

        $contextManager = $this->createMock('\Sonata\BlockBundle\Block\BlockContextManagerInterface');
        $contextManager->expects($this->once())->method('get')->will($this->returnValue($blockContext));

        $controller = new AjaxController($selector, $renderer, $contextManager);

        $request = new Request();

        $response = $controller->execute($request, 10, 12);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }
}
