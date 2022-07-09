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

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Validator\Constraints\UniqueUrl;
use Sonata\PageBundle\Validator\UniqueUrlValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class UniqueUrlValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @var PageManagerInterface
     */
    protected $manager;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(PageManagerInterface::class);

        parent::setUp();
    }

    public function testValidateWithoutSite(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::exactly(1))->method('getSite')->willReturn(null);
        $page->expects(static::never())->method('isError');

        $this->manager->expects(static::never())->method('fixUrl');
        $this->manager->expects(static::never())->method('findBy');

        $this->validator->validate($page, new UniqueUrl());

        $this->buildViolation('error.uniq_url.no_site')
            ->atPath($this->propertyPath.'.site')
            ->assertRaised();
    }

    /**
     * @group legacy
     */
    public function testValidateWithNoPageFound(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects(static::exactly(2))->method('getSite')->willReturn($site);
        $page->expects(static::exactly(2))->method('isError')->willReturn(false);

        $this->manager->expects(static::once())->method('fixUrl');
        $this->manager->expects(static::once())->method('findBy')->willReturn([$page]);

        $this->validator->validate($page, new UniqueUrl());

        $this->assertNoViolation();
    }

    public function testValidateWithPageFound(): void
    {
        $url = '/salut';
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects(static::exactly(2))->method('getSite')->willReturn($site);
        $page->expects(static::exactly(2))->method('isError')->willReturn(false);
        $page->method('getUrl')->willReturn($url);

        $pageFound = $this->createMock(PageInterface::class);
        $pageFound->method('getUrl')->willReturn($url);

        $this->manager->expects(static::once())->method('fixUrl');
        $this->manager->expects(static::once())->method('findBy')->willReturn([$page, $pageFound]);

        $this->validator->validate($page, new UniqueUrl());

        $this->buildViolation('error.uniq_url')
            ->setParameter('%url%', $url)
            ->atPath($this->propertyPath.'.url')
            ->assertRaised();
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

        $this->manager->expects(static::once())->method('fixUrl');
        $this->manager->expects(static::once())->method('findBy')->willReturn([$page, $pageFound]);

        $this->validator->validate($page, new UniqueUrl());

        $this->buildViolation('error.uniq_url.parent_unselect')
            ->atPath($this->propertyPath.'.parent')
            ->assertRaised();
    }

    public function testValidateWithPageDynamic(): void
    {
        $site = $this->createMock(SiteInterface::class);

        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getSite')->willReturn($site);
        $page->expects(static::once())->method('isError')->willReturn(false);
        $page->expects(static::once())->method('isDynamic')->willReturn(true);
        $page->method('getUrl')->willReturn('/salut');

        $this->validator->validate($page, new UniqueUrl());
        $this->assertNoViolation();
    }

    protected function createValidator(): UniqueUrlValidator
    {
        return new UniqueUrlValidator($this->manager);
    }
}
