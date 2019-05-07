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

namespace Sonata\PageBundle\Tests\Page;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\Template;
use Sonata\PageBundle\Page\TemplateManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\StreamingEngineInterface;

class TemplateManagerTest extends TestCase
{
    /**
     * Test adding a new template.
     */
    public function testAddSingleTemplate()
    {
        $template = $this->getMockTemplate('template');
        $templating = $this->createMock(EngineInterface::class);
        $manager = new TemplateManager($templating);

        $manager->add('code', $template);

        $this->assertSame($template, $manager->get('code'));
    }

    /**
     * Test setting all templates.
     */
    public function testSetAllTemplates()
    {
        $templating = $this->createMock(EngineInterface::class);
        $manager = new TemplateManager($templating);

        $templates = [
            'test1' => $this->getMockTemplate('template'),
            'test2' => $this->getMockTemplate('template'),
        ];

        $manager->setAll($templates);

        $this->assertSame($templates['test1'], $manager->get('test1'));
        $this->assertSame($templates['test2'], $manager->get('test2'));
        $this->assertSame($templates, $manager->getAll());
    }

    /**
     * Test setting the default template code.
     */
    public function testSetDefaultTemplateCode()
    {
        $templating = $this->createMock(EngineInterface::class);
        $manager = new TemplateManager($templating);

        $manager->setDefaultTemplateCode('test');

        $this->assertSame('test', $manager->getDefaultTemplateCode());
    }

    /**
     * test the rendering of a response.
     */
    public function testRenderResponse()
    {
        $template = $this->getMockTemplate('template', 'path/to/template');

        $response = $this->createMock(Response::class);
        $templating = $this->createMock(EngineInterface::class);
        $templating
            ->expects($this->once())
            ->method('renderResponse')
            ->with($this->equalTo('path/to/template'))
            ->willReturn($response);

        $manager = new TemplateManager($templating);
        $manager->add('test', $template);

        $this->assertSame(
            $response,
            $manager->renderResponse('test'),
            'should return the mocked response'
        );
    }

    /**
     * test the rendering of a response with a non existing template code.
     */
    public function testRenderResponseWithNonExistingCode()
    {
        $templating = $this->createMock(EngineInterface::class);
        $templating
            ->expects($this->once())
            ->method('renderResponse')
            ->with($this->equalTo('@SonataPage/layout.html.twig'));
        $manager = new TemplateManager($templating);

        $manager->renderResponse('test');
    }

    /**
     * test the rendering of a response with no template code.
     */
    public function testRenderResponseWithoutCode()
    {
        $response = $this->createMock(Response::class);
        $templating = $this->createMock(EngineInterface::class);
        $templating
            ->expects($this->once())
            ->method('renderResponse')
            ->with($this->equalTo('path/to/default'))
            ->willReturn($response);

        $template = $this->getMockTemplate('template', 'path/to/default');
        $manager = new TemplateManager($templating);
        $manager->add('default', $template);
        $manager->setDefaultTemplateCode('default');

        $this->assertSame(
            $response,
            $manager->renderResponse(null),
            'should return the mocked response'
        );
    }

    /**
     * test the rendering of a response with default parameters.
     */
    public function testRenderResponseWithDefaultParameters()
    {
        $template = $this->getMockTemplate('template', 'path/to/template');

        $response = $this->createMock(Response::class);
        $templating = $this->createMock(EngineInterface::class);
        $templating->expects($this->once())->method('renderResponse')
            ->with(
                $this->equalTo('path/to/template'),
                $this->equalTo(['parameter1' => 'value', 'parameter2' => 'value'])
            )
            ->willReturn($response);

        $defaultParameters = ['parameter1' => 'value'];

        $manager = new TemplateManager($templating, $defaultParameters);
        $manager->add('test', $template);

        $this->assertSame(
            $response,
            $manager->renderResponse('test', ['parameter2' => 'value']),
            'should return the mocked response'
        );
    }

    /**
     * Returns the mock template.
     */
    protected function getMockTemplate(string $name, string $path = 'path/to/file'): MockObject
    {
        $template = $this->createMock(Template::class);
        $template->expects($this->any())->method('getName')->willReturn($name);
        $template->expects($this->any())->method('getPath')->willReturn($path);

        return $template;
    }
}

abstract class MockTemplating implements EngineInterface, StreamingEngineInterface
{
}
