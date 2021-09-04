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
    public function testValidateWithNoPageFound(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects(static::exactly(2))->method('getSite')->willReturn($site);
        $page->expects(static::exactly(2))->method('isError')->willReturn(false);

        $manager = $this->createMock(PageManagerInterface::class);
        $manager->expects(static::once())->method('fixUrl');
        $manager->expects(static::once())->method('findBy')->willReturn([$page]);

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithPageFound(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects(static::exactly(2))->method('getSite')->willReturn($site);
        $page->expects(static::exactly(2))->method('isError')->willReturn(false);
        $page->method('getUrl')->willReturn('/salut');

        $pageFound = $this->createMock(PageInterface::class);
        $pageFound->method('getUrl')->willReturn('/salut');

        $manager = $this->createMock(PageManagerInterface::class);
        $manager->expects(static::once())->method('fixUrl');
        $manager->expects(static::once())->method('findBy')->willReturn([$page, $pageFound]);

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithRootUrlAndNoParent(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects(static::exactly(2))->method('getSite')->willReturn($site);
        $page->expects(static::exactly(2))->method('isError')->willReturn(false);
        $page->expects(static::once())->method('getParent')->willReturn(null);
        $page->method('getUrl')->willReturn('/');

        $pageFound = $this->createMock(PageInterface::class);
        $pageFound->method('getUrl')->willReturn('/');

        $manager = $this->createMock(PageManagerInterface::class);
        $manager->expects(static::once())->method('fixUrl');
        $manager->expects(static::once())->method('findBy')->willReturn([$page, $pageFound]);

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    public function testValidateWithPageDynamic(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getSite')->willReturn($site);
        $page->expects(static::once())->method('isError')->willReturn(false);
        $page->expects(static::once())->method('isDynamic')->willReturn(true);
        $page->method('getUrl')->willReturn('/salut');

        $manager = $this->createMock(PageManagerInterface::class);

        $context = $this->getContext();

        $validator = new UniqueUrlValidator($manager);
        $validator->initialize($context);

        $validator->validate($page, new UniqueUrl());
    }

    private function getContext(): ExecutionContext
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $validator = $this->createMock(ValidatorInterface::class);
        $contextualValidator = $this->createMock(ContextualValidatorInterface::class);

        $context = new ExecutionContext($validator, 'root', $translator);

        $context->setGroup('MyGroup');
        $context->setNode('InvalidValue', null, null, 'property.path');
        $context->setConstraint(new UniqueUrl());
        $validator
            ->method('inContext')
            ->with($context)
            ->willReturn($contextualValidator);

        return $context;
    }
}
