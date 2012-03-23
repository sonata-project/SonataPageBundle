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

class TemplateChoiceTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOptions()
    {
        $rendered = $this->getMock('Sonata\PageBundle\CmsManager\PageRendererInterface');
        $rendered->expects($this->once())->method('getTemplates')->will($this->returnValue(array()));

        $type = new TemplateChoiceType($rendered);
        $type->getDefaultOptions(array());

    }
}