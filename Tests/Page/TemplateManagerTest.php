<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Page;

use Sonata\PageBundle\Page\TemplateManager;
use Symfony\Component\Templating\StreamingEngineInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Test the template manager
 */
class TemplateManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test adding a new template
     */
    public function testAddSingleTemplate()
    {
        // GIVEN
        $template = $this->getMockTemplate('template');
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $manager = new TemplateManager($templating);

        // WHEN
        $manager->add('code', $template);

        // THEN
        $this->assertEquals($template, $manager->get('code'));
    }

    /**
     * Test setting all templates
     */
    public function testSetAllTemplates()
    {
        // GIVEN
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $manager = new TemplateManager($templating);

        $templates = array(
            'test1' => $this->getMockTemplate('template'),
            'test2' => $this->getMockTemplate('template')
        );

        // WHEN
        $manager->setAll($templates);

        // THEN
        $this->assertEquals($templates['test1'], $manager->get('test1'));
        $this->assertEquals($templates['test2'], $manager->get('test2'));
        $this->assertEquals($templates, $manager->getAll());
    }

    /**
     * Test setting the default template code
     */
    public function testSetDefaultTemplateCode()
    {
        // GIVEN
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $manager = new TemplateManager($templating);

        // WHEN
        $manager->setDefaultTemplateCode('test');

        // THEN
        $this->assertEquals('test', $manager->getDefaultTemplateCode());
    }

    /**
     * test the rendering of a response
     */
    public function testRenderResponse()
    {
        // GIVEN
        $template = $this->getMockTemplate('template', 'path/to/template');

        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating->expects($this->once())->method('renderResponse')->with($this->equalTo('path/to/template'))->will($this->returnValue($response));

        $manager = new TemplateManager($templating);
        $manager->add('test', $template);

        // WHEN
        $result = $manager->renderResponse('test');

        // THEN
        $this->assertEquals($response, $result, 'should return the mocked response');
    }

    /**
     * test the rendering of a response with a non existing template code
     */
    public function testRenderResponseWithNonExistingCode()
    {
        // GIVEN
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating->expects($this->once())->method('renderResponse')->with($this->equalTo('SonataPageBundle::layout.html.twig'));
        $manager = new TemplateManager($templating);

        // WHEN
        $manager->renderResponse('test');

        // THEN
        // mock asserts
    }

    /**
     * test the rendering of a response with no template code
     */
    public function testRenderResponseWithoutCode()
    {
        // GIVEN
        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating->expects($this->once())->method('renderResponse')->with($this->equalTo('path/to/default'))->will($this->returnValue($response));

        $template = $this->getMockTemplate('template', 'path/to/default');
        $manager = new TemplateManager($templating);
        $manager->add('default', $template);
        $manager->setDefaultTemplateCode('default');

        // WHEN
        $result = $manager->renderResponse(null);

        // THEN
        $this->assertEquals($response, $result, 'should return the mocked response');
    }

    /**
     * test the rendering of a response with default parameters
     */
    public function testRenderResponseWithDefaultParameters()
    {
        // GIVEN
        $template = $this->getMockTemplate('template', 'path/to/template');

        $response = $this->getMock('Symfony\Component\HttpFoundation\Response');
        $templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating->expects($this->once())->method('renderResponse')
            ->with(
                $this->equalTo('path/to/template'),
                $this->equalTo(array('parameter1' => 'value', 'parameter2' => 'value'))
            )
            ->will($this->returnValue($response));

        $defaultParameters = array('parameter1' => 'value');

        $manager = new TemplateManager($templating, $defaultParameters);
        $manager->add('test', $template);

        // WHEN
        $result = $manager->renderResponse('test', array('parameter2' => 'value'));

        // THEN
        $this->assertEquals($response, $result, 'should return the mocked response');
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

abstract class MockTemplating implements EngineInterface, StreamingEngineInterface
{}
