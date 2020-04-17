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

namespace Sonata\PageBundle\Tests\Form\Type;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Form\Type\TemplateChoiceType;
use Sonata\PageBundle\Model\Template;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateChoiceTypeTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var TemplateChoiceType
     */
    protected $type;

    /**
     * setup each unit test.
     */
    protected function setUp(): void
    {
        $this->manager = $this->createMock(TemplateManagerInterface::class);
        $this->type = new TemplateChoiceType($this->manager);
    }

    /**
     * Test getting options.
     */
    public function testGetOptions(): void
    {
        $this->manager->expects($this->atLeastOnce())->method('getAll')->willReturn([
            'my_template' => $this->getMockTemplate('Template 1'),
        ]);

        $this->type->configureOptions(new OptionsResolver());

        $this->type->getTemplates();
        $this->assertSame(
            ['Template 1' => 'my_template'],
            $this->type->getTemplates(),
            'Should return an array of templates provided by the template manager'
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
