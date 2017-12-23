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
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Validator\Constraints\UniqueUrl;
use Sonata\PageBundle\Validator\UniqueUrlValidator;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UniqueUrlValidatorTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testValidateWithNoPageFound()
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));

        $manager = $this->createMock(PageManagerInterface::class);
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue([$page]));

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithPageFound()
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));
        $page->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $pageFound = $this->createMock(PageInterface::class);
        $pageFound->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $manager = $this->createMock(PageManagerInterface::class);
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue([$page, $pageFound]));

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithRootUrlAndNoParent()
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects($this->exactly(2))->method('getSite')->will($this->returnValue($site));
        $page->expects($this->exactly(2))->method('isError')->will($this->returnValue(false));
        $page->expects($this->exactly(1))->method('getParent')->will($this->returnValue(null));
        $page->expects($this->any())->method('getUrl')->will($this->returnValue('/'));

        $pageFound = $this->createMock(PageInterface::class);
        $pageFound->expects($this->any())->method('getUrl')->will($this->returnValue('/'));

        $manager = $this->createMock(PageManagerInterface::class);
        $manager->expects($this->once())->method('fixUrl');
        $manager->expects($this->once())->method('findBy')->will($this->returnValue([$page, $pageFound]));

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithPageDynamic()
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('getSite')->will($this->returnValue($site));
        $page->expects($this->once())->method('isError')->will($this->returnValue(false));
        $page->expects($this->once())->method('isDynamic')->will($this->returnValue(true));
        $page->expects($this->any())->method('getUrl')->will($this->returnValue('/salut'));

        $manager = $this->createMock(PageManagerInterface::class);

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    private function getContext()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);

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
