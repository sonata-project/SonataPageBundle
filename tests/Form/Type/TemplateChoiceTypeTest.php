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

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Form\Type\TemplateChoiceType;
use Sonata\PageBundle\Model\Template;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Test the template choice form type.
 */
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
    public function setUp(): void
    {
        $this->manager = $this->createMock(TemplateManagerInterface::class);
        $this->type = new TemplateChoiceType($this->manager);
    }

    /**
     * Test getting options.
     */
    public function testGetOptions(): void
    {
        // GIVEN
        $template = $this->getMockTemplate('Template 1');

        $this->manager->expects($this->atLeastOnce())->method('getAll')->will($this->returnValue([
            'my_template' => $template,
        ]));

        // WHEN
        $this->type->configureOptions(new OptionsResolver());

        // THEN
        $this->type->getTemplates();
        $this->assertEquals(['Template 1' => 'my_template'], $this->type->getTemplates(),
            'Should return an array of templates provided by the template manager');
    }

    /**
     * Returns the mock template.
     *
     * @param string $name Name of the template
     * @param string $path Path to the file of the template
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockTemplate($name, $path = 'path/to/file')
    {
        $template = $this->getMockbuilder(Template::class)->disableOriginalConstructor()->getMock();
        $template->expects($this->any())->method('getName')->will($this->returnValue($name));
        $template->expects($this->any())->method('getPath')->will($this->returnValue($path));

        return $template;
    }
}
