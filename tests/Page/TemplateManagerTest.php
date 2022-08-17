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
use Sonata\PageBundle\CmsManager\CmsSnapshotManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\Template;
use Sonata\PageBundle\Page\TemplateManager;
use Sonata\PageBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\StreamingEngineInterface;

final class TemplateManagerTest extends KernelTestCase
{
    /**
     * Test adding a new template.
     */
    public function testAddSingleTemplate(): void
    {
        $template = $this->getMockTemplate('template');
        $templating = $this->createMock(EngineInterface::class);
        $manager = new TemplateManager($templating);

        $manager->add('code', $template);

        static::assertSame($template, $manager->get('code'));
    }

    /**
     * Test setting all templates.
     */
    public function testSetAllTemplates(): void
    {
        $templating = $this->createMock(EngineInterface::class);
        $manager = new TemplateManager($templating);

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
        $templating = $this->createMock(EngineInterface::class);
        $manager = new TemplateManager($templating);

        $manager->setDefaultTemplateCode('test');

        static::assertSame('test', $manager->getDefaultTemplateCode());
    }

    /**
     * test the rendering of a response.
     */
    public function testRenderResponse(): void
    {
        $template = $this->getMockTemplate('template', 'path/to/template');

        $response = $this->createMock(Response::class);
        $templating = $this->createMock(EngineInterface::class);
        $templating
            ->expects(static::once())
            ->method('renderResponse')
            ->with(static::equalTo('path/to/template'))
            ->willReturn($response);

        $manager = new TemplateManager($templating);
        $manager->add('test', $template);

        static::assertSame(
            $response,
            $manager->renderResponse('test'),
            'should return the mocked response'
        );
    }

    /**
     * test the rendering of a response with a non existing template code.
     */
    public function testRenderResponseWithNonExistingCode(): void
    {
        $templating = $this->createMock(EngineInterface::class);
        $templating
            ->expects(static::once())
            ->method('renderResponse')
            ->with(static::equalTo('@SonataPage/layout.html.twig'));
        $manager = new TemplateManager($templating);

        $manager->renderResponse('test');
    }

    /**
     * test the rendering of a response with no template code.
     */
    public function testRenderResponseWithoutCode(): void
    {
        $response = $this->createMock(Response::class);
        $templating = $this->createMock(EngineInterface::class);
        $templating
            ->expects(static::once())
            ->method('renderResponse')
            ->with(static::equalTo('path/to/default'))
            ->willReturn($response);

        $template = $this->getMockTemplate('template', 'path/to/default');
        $manager = new TemplateManager($templating);
        $manager->add('default', $template);
        $manager->setDefaultTemplateCode('default');

        static::assertSame(
            $response,
            $manager->renderResponse(null),
            'should return the mocked response'
        );
    }

    /**
     * test the rendering of a response with default parameters.
     */
    public function testRenderResponseWithDefaultParameters(): void
    {
        $template = $this->getMockTemplate('template', 'path/to/template');

        $response = $this->createMock(Response::class);
        $templating = $this->createMock(EngineInterface::class);
        $templating->expects(static::once())->method('renderResponse')
            ->with(
                static::equalTo('path/to/template'),
                static::equalTo(['parameter1' => 'value', 'parameter2' => 'value'])
            )
            ->willReturn($response);

        $defaultParameters = ['parameter1' => 'value'];

        $manager = new TemplateManager($templating, $defaultParameters);
        $manager->add('test', $template);

        static::assertSame(
            $response,
            $manager->renderResponse('test', ['parameter2' => 'value']),
            'should return the mocked response'
        );
    }

    /**
     * NEXT_MAJOR: Remove the legacy group.
     *
     * @group legacy
     */
    public function testTemplateShowingBreadcrumbIntoThePage(): void
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

        $requestStack = new RequestStack();
        $requestStack->push(new Request());
        $container->set('request_stack', $requestStack);

        //Mocking snapshot
        $pageMock = $this->createMock(PageInterface::class);
        $pageMock->method('getName')->willReturn('Foo');
        $pageMock->method('getParents')->willReturn([]);
        $pageMock->method('getUrl')->willReturn('/');

        $cmsSnapshotManagerMock = $this->createMock(CmsSnapshotManager::class);
        $cmsSnapshotManagerMock->method('getCurrentPage')->willReturn($pageMock);
        $container->set('sonata.page.cms.snapshot', $cmsSnapshotManagerMock);

        //NEXT_MAJOR: change for twig
        $templating = $container->get('templating');

        $manager = new TemplateManager($templating, []);
        $response = $manager->renderResponse('test');
        $crawler = new Crawler($response->getContent());

        static::assertCount(1, $crawler->filter('.page-breadcrumb'));

        $breadcrumbFoo = $crawler->filter('.page-breadcrumb')->filter('a');
        static::assertSame('/', $breadcrumbFoo->attr('href'));
        static::assertStringContainsString('Foo', $breadcrumbFoo->text());
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
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

abstract class MockTemplating implements EngineInterface, StreamingEngineInterface
{
}
