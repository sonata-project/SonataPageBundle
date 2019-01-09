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
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\BlockBundle\Exception\BlockNotFoundException;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Controller\AjaxController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
class AjaxControllerTest extends TestCase
{
    public function testWithInvalidBlock(): void
    {
        $this->expectException(BlockNotFoundException::class);

        $cmsManager = $this->createMock(CmsManagerInterface::class);

        $selector = $this->createMock(CmsManagerSelectorInterface::class);
        $selector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $renderer = $this->createMock(BlockRendererInterface::class);

        $contextManager = $this->createMock(BlockContextManagerInterface::class);

        $controller = new AjaxController($selector, $renderer, $contextManager);

        $request = new Request();

        $controller->execute($request, 10, 12);
    }

    public function testRenderer(): void
    {
        $block = $this->createMock(BlockInterface::class);

        $cmsManager = $this->createMock(CmsManagerInterface::class);
        $cmsManager->expects($this->once())->method('getBlock')->will($this->returnValue($block));

        $selector = $this->createMock(CmsManagerSelectorInterface::class);
        $selector->expects($this->once())->method('retrieve')->will($this->returnValue($cmsManager));

        $renderer = $this->createMock(BlockRendererInterface::class);
        $renderer->expects($this->once())->method('render')->will($this->returnValue(new Response()));

        $blockContext = $this->createMock(BlockContextInterface::class);

        $contextManager = $this->createMock(BlockContextManagerInterface::class);
        $contextManager->expects($this->once())->method('get')->will($this->returnValue($blockContext));

        $controller = new AjaxController($selector, $renderer, $contextManager);

        $request = new Request();

        $response = $controller->execute($request, 10, 12);

        $this->assertInstanceOf(Response::class, $response);
    }
}
