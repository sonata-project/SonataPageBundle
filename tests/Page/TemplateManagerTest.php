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

use Sonata\PageBundle\Model\Template;
use Sonata\PageBundle\Page\TemplateManager;
use Sonata\PageBundle\Tests\App\AppKernel;
use Twig\Environment;

final class TemplateManagerTest extends KernelTestCase
{
    public function testAddSingleTemplate(): void
    {
        $template = $this->getTemplate('template');
        $twig = $this->createMock(Environment::class);
        $manager = new TemplateManager($twig);

        $manager->add('code', $template);

        static::assertSame($template, $manager->get('code'));
    }

    public function testSetAllTemplates(): void
    {
        $twig = $this->createMock(Environment::class);
        $manager = new TemplateManager($twig);

        $templates = [
            'test1' => $this->getTemplate('template'),
            'test2' => $this->getTemplate('template'),
        ];

        $manager->setAll($templates);

        static::assertSame($templates['test1'], $manager->get('test1'));
        static::assertSame($templates['test2'], $manager->get('test2'));
        static::assertSame($templates, $manager->getAll());
    }

    public function testSetDefaultTemplateCode(): void
    {
        $twig = $this->createMock(Environment::class);
        $manager = new TemplateManager($twig);

        $manager->setDefaultTemplateCode('test');

        static::assertSame('test', $manager->getDefaultTemplateCode());
    }

    public function testRenderResponse(): void
    {
        $template = $this->getTemplate('template', 'path/to/template');

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

    public function testRenderResponseWithoutCode(): void
    {
        $twig = $this->createMock(Environment::class);
        $twig
            ->expects(static::once())
            ->method('render')
            ->with(static::equalTo('path/to/default'))
            ->willReturn('response');

        $template = $this->getTemplate('template', 'path/to/default');
        $manager = new TemplateManager($twig);
        $manager->add('default', $template);
        $manager->setDefaultTemplateCode('default');

        static::assertSame(
            'response',
            $manager->renderResponse(null)->getContent(),
            'should return the mocked response'
        );
    }

    public function testRenderResponseWithDefaultParameters(): void
    {
        $template = $this->getTemplate('template', 'path/to/template');

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
    private function getTemplate(string $name, string $path = 'path/to/file'): Template
    {
        return new Template($name, $path);
    }
}
