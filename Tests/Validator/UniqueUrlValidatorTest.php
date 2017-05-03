<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Validator;

use Sonata\PageBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Sonata\PageBundle\Validator\Constraints\UniqueUrl;
use Sonata\PageBundle\Validator\UniqueUrlValidator;

class UniqueUrlValidatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group legacy
     */
    public function testValidateWithNoPageFound()
    {
        $this->skipInPHP55();
        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));

        $manager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue(array($page)));

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithPageFound()
    {
        $this->skipInPHP55();
        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));
        $page->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $pageFound = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $pageFound->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $manager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue(array($page, $pageFound)));

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithRootUrlAndNoParent()
    {
        $this->skipInPHP55();
        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));
        $page->expects($this->exactly(1))->method('getParent')->will($this->returnValue(null));
        $page->expects($this->any())->method('getUrl')->will($this->returnValue('/'));

        $pageFound = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $pageFound->expects($this->any())->method('getUrl')->will($this->returnValue('/'));

        $manager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue(array($page, $pageFound)));

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithPageDynamic()
    {
        $this->skipInPHP55();
        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getSite')->will($this->returnValue($site));
        $page->expects($this->once())->method('isError')->will($this->returnValue(false));
        $page->expects($this->once())->method('isDynamic')->will($this->returnValue(true));
        $page->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $manager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    private function getContext()
    {
        return $this->createMock(
            class_exists('Symfony\Component\Validator\Context\ExecutionContextInterface') ?
            'Symfony\Component\Validator\Context\ExecutionContextInterface' :
            'Symfony\Component\Validator\ExecutionContextInterface'
        );
    }

    private function skipInPHP55()
    {
        if (version_compare(PHP_VERSION, '5.5.0', '>=') && version_compare(PHP_VERSION, '5.6.0', '<=')) {
            $this->markTestSkipped(
                'This test should be skipped in php 5.5 due to an issue with phpunit.'
            );
        }
    }
}
