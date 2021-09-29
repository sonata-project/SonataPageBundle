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

namespace Sonata\PageBundle\Tests\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BasePage;
use Sonata\PageBundle\Entity\PageManager;
use Sonata\PageBundle\Tests\Model\Page;

class PageManagerTest extends TestCase
{
    public function testFixUrl(): void
    {
        $manager = new PageManager(
            'Foo\Bar',
            $this->createMock(ManagerRegistry::class),
            []
        );

        $page1 = new Page();
        $page1->setName('Salut comment ca va ?');

        $page2 = new Page();
        $page2->setName('Super! et toi ?');

        $page1->addChildren($page2);

        $manager->fixUrl($page1);

        static::assertSame('', $page1->getSlug());
        static::assertSame('/', $page1->getUrl());

        // if a parent page becomes a child page, then the slug and the url must be updated
        $parent = new Page();
        $parent->addChildren($page1);

        $manager->fixUrl($parent);

        static::assertSame('', $parent->getSlug());
        static::assertSame('/', $parent->getUrl());

        static::assertSame('salut-comment-ca-va', $page1->getSlug());
        static::assertSame('/salut-comment-ca-va', $page1->getUrl());

        static::assertSame('super-et-toi', $page2->getSlug());
        static::assertSame('/salut-comment-ca-va/super-et-toi', $page2->getUrl());

        // check to remove the parent, so $page1 becomes a parent
        $page1->setParent(null);
        $manager->fixUrl($parent);

        static::assertSame('', $page1->getSlug());
        static::assertSame('/', $page1->getUrl());
    }

    public function testWithSlashAtTheEnd(): void
    {
        $manager = new PageManager(
            'Foo\Bar',
            $this->createMock(ManagerRegistry::class),
            []
        );

        $homepage = new Page();
        $homepage->setUrl('/');
        $homepage->setName('homepage');

        $bundle = new Page();
        $bundle->setUrl('/bundles/');
        $bundle->setName('Bundles');

        $child = new Page();
        $child->setName('foobar');

        $bundle->addChildren($child);
        $homepage->addChildren($bundle);

        $manager->fixUrl($child);

        static::assertSame('/bundles/foobar', $child->getUrl());
    }

    public function testCreateWithGlobalDefaults(): void
    {
        $manager = new PageManager(
            Page::class,
            $this->createMock(ManagerRegistry::class),
            [],
            ['my_route' => ['decorate' => false, 'name' => 'Salut!']]
        );

        $page = $manager->create(['name' => 'My Name', 'routeName' => 'my_route']);

        static::assertSame('My Name', $page->getName());
        static::assertFalse($page->getDecorate());
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPager(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('orderBy')->with(
                    $self->equalTo('p.name'),
                    $self->equalTo('ASC')
                );
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo([]));
            })
            ->getPager([], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithInvalidSort(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Invalid sort field \'invalid\' in \'Sonata\\PageBundle\\Entity\\BasePage\' class'
        );

        $this
            ->getPageManager(static function ($qb): void {
            })
            ->getPager([], 1, 10, ['invalid' => 'ASC']);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithMultipleSort(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->exactly(2))->method('orderBy')->with(
                    $self->logicalOr(
                        $self->equalTo('p.name'),
                        $self->equalTo('p.routeName')
                    ),
                    $self->logicalOr(
                        $self->equalTo('ASC'),
                        $self->equalTo('DESC')
                    )
                );
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo([]));
            })
            ->getPager([], 1, 10, [
                'name' => 'ASC',
                'routeName' => 'DESC',
            ]);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithRootPages(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.parent IS NULL'));
            })
            ->getPager(['root' => true], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithNonRootPages(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.parent IS NOT NULL'));
            })
            ->getPager(['root' => false], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithEnabledPages(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => true]));
            })
            ->getPager(['enabled' => true], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithDisabledPages(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => false]));
            })
            ->getPager(['enabled' => false], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithEditedPages(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.edited = :edited'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['edited' => true]));
            })
            ->getPager(['edited' => true], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithNonEditedPages(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.edited = :edited'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['edited' => false]));
            })
            ->getPager(['edited' => false], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithParentChildPages(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('p.parent'),
                    $self->equalTo('pa')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('pa.id = :parentId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['parentId' => 13]));
            })
            ->getPager(['parent' => 13], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithSitePages(): void
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self): void {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('p.site'),
                    $self->equalTo('s')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.id = :siteId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['siteId' => 13]));
            })
            ->getPager(['site' => 13], 1);
    }

    protected function getPageManager($qbCallback): PageManager
    {
        $query = $this->getMockForAbstractClass(AbstractQuery::class, [], '', false, true, true, ['execute']);
        $query->method('execute')->willReturn(true);

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$this->createMock(EntityManager::class)])
            ->getMock();

        $qb->method('getRootAliases')->willReturn([]);
        $qb->method('select')->willReturn($qb);
        $qb->method('getQuery')->willReturn($query);

        $qbCallback($qb);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')->willReturn($qb);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->method('getFieldNames')->willReturn([
            'name',
            'routeName',
        ]);

        $em = $this->createStub(EntityManager::class);
        $em->method('getRepository')->willReturn($repository);
        $em->method('getClassMetadata')->willReturn($metadata);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        return new PageManager(BasePage::class, $registry);
    }
}
