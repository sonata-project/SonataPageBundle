<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Validator;

use Sonata\PageBundle\Validator\UniqueUrlValidator;
use Sonata\PageBundle\Validator\Constraints\UniqueUrl;

class UniqueUrlValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateWithNoPageFound()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));

        $manager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue(array($page)));

        $context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $context->expects($this->never())->method('addViolation');

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl);
    }

    public function testValidateWithPageFound()
    {
        $site = $this->getMock('Sonata\PageBundle\Model\SiteInterface');

        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));
        $page->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $pageFound = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $pageFound->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $manager = $this->getMock('Sonata\PageBundle\Model\PageManagerInterface');
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue(array($page, $pageFound)));

        $context = $this->getMock('Symfony\Component\Validator\ExecutionContext', array(), array(), '', false);
        $context->expects($this->once())->method('addViolation');

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl);
    }
}
