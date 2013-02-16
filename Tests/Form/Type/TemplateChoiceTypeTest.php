<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Form\Type;

use Sonata\PageBundle\Form\Type\TemplateChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Test the template choice form type
 */
class TemplateChoiceTypeTest extends \PHPUnit_Framework_TestCase
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
     * setup each unit test
     */
    public function setUp()
    {
        $this->manager = $this->getMock('Sonata\PageBundle\Page\TemplateManagerInterface');
        $this->type = new TemplateChoiceType($this->manager);
    }

    /**
     * Test getting options
     */
    public function testGetOptions()
    {
        // GIVEN
        $template = $this->getMockTemplate('Template 1');

        $this->manager->expects($this->atLeastOnce())->method('getAll')->will($this->returnValue(array(
            'my_template' => $template
        )));

        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');

        // WHEN
        $this->type->setDefaultOptions($resolver);

        // THEN
        $this->type->getTemplates();
        $this->assertEquals(array('my_template' => 'Template 1'), $this->type->getTemplates(),
            'Should return an array of templates provided by the template manager');
    }

    /**
     * Returns the mock template
     *
     * @param string $name Name of the template
     * @param string $path Path to the file of the template
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockTemplate($name, $path = 'path/to/file')
    {
        $template = $this->getMockbuilder('Sonata\PageBundle\Model\Template')->disableOriginalConstructor()->getMock();
        $template->expects($this->any())->method('getName')->will($this->returnValue($name));
        $template->expects($this->any())->method('getPath')->will($this->returnValue($path));

        return $template;
    }
}
