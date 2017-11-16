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

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Validator\Constraints\UniqueUrl;
use Sonata\PageBundle\Validator\UniqueUrlValidator;
use Symfony\Component\Validator\Context\ExecutionContext;

class UniqueUrlValidatorTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testValidateWithNoPageFound()
    {
        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));

        $manager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue([$page]));

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithPageFound()
    {
        $site = $this->createMock('Sonata\PageBundle\Model\SiteInterface');

        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));
        $page->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $pageFound = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $pageFound->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $manager = $this->createMock('Sonata\PageBundle\Model\PageManagerInterface');
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue([$page, $pageFound]));

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithRootUrlAndNoParent()
    {
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
        $manager->expects($this->once())->method('findBy')->will($this->returnValue([$page, $pageFound]));

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithPageDynamic()
    {
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
        $translator = $this->createMock('Symfony\Component\Translation\TranslatorInterface');
        $validator = $this->createMock('Symfony\Component\Validator\Validator\ValidatorInterface');
        $contextualValidator = $this->createMock('Symfony\Component\Validator\Validator\ContextualValidatorInterface');

        $context = new ExecutionContext($validator, 'root', $translator);

        $context->setGroup('MyGroup');
        $context->setNode('InvalidValue', null, null, 'property.path');
        $context->setConstraint(new UniqueUrl());
        $validator->expects($this->any())
            ->method('inContext')
            ->with($context)
            ->will($this->returnValue($contextualValidator));

        return $context;
    }
}
