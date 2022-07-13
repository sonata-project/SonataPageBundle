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
use Twig\Environment;

final class TemplateManagerTest extends TestCase
{
    /**
     * Test adding a new template.
     */
    public function testAddSingleTemplate(): void
    {
        /** @var Template|MockObject $template */
        $template = $this->getMockTemplate('template');
        $twig = $this->createMock(Environment::class);
        $manager = new TemplateManager($twig);

        $manager->add('code', $template);

        static::assertSame($template, $manager->get('code'));
    }

    /**
     * Test setting all templates.
     */
    public function testSetAllTemplates(): void
    {
        $twig = $this->createMock(Environment::class);
        $manager = new TemplateManager($twig);

        $templates = [
            'test1' => $this->getMockTemplate('template'),
            'test2' => $this->getMockTemplate('template'),
        ];

        $manager->setAll($templates);

        static::assertSame($templates['test1'], $manager->get('test1'));
        static::assertSame($templates['test2'], $manager->get('test2'));
        static::assertSame($templates, $manager->getAll());
    }

    /**
     * Test setting the default template code.
     */
    public function testSetDefaultTemplateCode(): void
    {
        $twig = $this->createMock(Environment::class);
        $manager = new TemplateManager($twig);

        $manager->setDefaultTemplateCode('test');

        static::assertSame('test', $manager->getDefaultTemplateCode());
    }

    /**
     * test the rendering of a response.
     */
    public function testRenderResponse(): void
    {
        $template = $this->getMockTemplate('template', 'path/to/template');

        $twig = $this->createMock(Environment::class);
        $twig
            ->expects(static::once())
            ->method('render')
            ->with(static::equalTo('path/to/template'))
            ->willReturn('response');

        $manager = new TemplateManager($twig);
        $manager->add('test', $template);

        static::assertSame(
            'response',
            $manager->renderResponse('test')->getContent(),
            'should return the mocked response'
        );
    }

    /**
     * test the rendering of a response with a non existing template code.
     */
    public function testRenderResponseWithNonExistingCode(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig
            ->expects(static::once())
            ->method('render')
            ->with(static::equalTo('@SonataPage/layout.html.twig'));
        $manager = new TemplateManager($twig);

        $manager->renderResponse('test');
    }

    /**
     * test the rendering of a response with no template code.
     */
    public function testRenderResponseWithoutCode(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig
            ->expects(static::once())
            ->method('render')
            ->with(static::equalTo('path/to/default'))
            ->willReturn('response');

        $template = $this->getMockTemplate('template', 'path/to/default');
        $manager = new TemplateManager($twig);
        $manager->add('default', $template);
        $manager->setDefaultTemplateCode('default');

        static::assertSame(
            'response',
            $manager->renderResponse('')->getContent(),
            'should return the mocked response'
        );
    }

    /**
     * test the rendering of a response with default parameters.
     */
    public function testRenderResponseWithDefaultParameters(): void
    {
        $template = $this->getMockTemplate('template', 'path/to/template');

        $twig = $this->createMock(Environment::class);
        $twig->expects(static::once())->method('render')
            ->with(
                static::equalTo('path/to/template'),
                static::equalTo(['parameter1' => 'value', 'parameter2' => 'value'])
            )
            ->willReturn('response');

        $defaultParameters = ['parameter1' => 'value'];

        $manager = new TemplateManager($twig, $defaultParameters);
        $manager->add('test', $template);

        static::assertSame(
            'response',
            $manager->renderResponse('test', ['parameter2' => 'value'])->getContent(),
            'should return the mocked response'
        );
    }

    /**
     * Returns the mock template.
     */
    protected function getMockTemplate(string $name, string $path = 'path/to/file'): MockObject
    {
        $template = $this->createMock(Template::class);
        $template->method('getName')->willReturn($name);
        $template->method('getPath')->willReturn($path);

        return $template;
    }
}
