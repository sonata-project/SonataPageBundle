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
use Doctrine\ORM\EntityManagerInterface;
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
    public function testFixUrl()
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

        $this->assertSame('', $page1->getSlug());
        $this->assertSame('/', $page1->getUrl());

        // if a parent page becomes a child page, then the slug and the url must be updated
        $parent = new Page();
        $parent->addChildren($page1);

        $manager->fixUrl($parent);

        $this->assertSame('', $parent->getSlug());
        $this->assertSame('/', $parent->getUrl());

        $this->assertSame('salut-comment-ca-va', $page1->getSlug());
        $this->assertSame('/salut-comment-ca-va', $page1->getUrl());

        $this->assertSame('super-et-toi', $page2->getSlug());
        $this->assertSame('/salut-comment-ca-va/super-et-toi', $page2->getUrl());

        // check to remove the parent, so $page1 becomes a parent
        $page1->setParent(null);
        $manager->fixUrl($parent);

        $this->assertSame('', $page1->getSlug());
        $this->assertSame('/', $page1->getUrl());
    }

    public function testWithSlashAtTheEnd()
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

        $this->assertSame('/bundles/foobar', $child->getUrl());
    }

    public function testCreateWithGlobalDefaults()
    {
        $manager = new PageManager(
            Page::class,
            $this->createMock(ManagerRegistry::class),
            [],
            ['my_route' => ['decorate' => false, 'name' => 'Salut!']]
        );

        $page = $manager->create(['name' => 'My Name', 'routeName' => 'my_route']);

        $this->assertSame('My Name', $page->getName());
        $this->assertFalse($page->getDecorate());
    }

    public function testGetPager()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('orderBy')->with(
                    $self->equalTo('p.name'),
                    $self->equalTo('ASC')
                );
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo([]));
            })
            ->getPager([], 1);
    }

    public function testGetPagerWithInvalidSort()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Invalid sort field \'invalid\' in \'Sonata\\PageBundle\\Entity\\BasePage\' class'
        );

        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
            })
            ->getPager([], 1, 10, ['invalid' => 'ASC']);
    }

    public function testGetPagerWithMultipleSort()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
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

    public function testGetPagerWithRootPages()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.parent IS NULL'));
            })
            ->getPager(['root' => true], 1);
    }

    public function testGetPagerWithNonRootPages()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.parent IS NOT NULL'));
            })
            ->getPager(['root' => false], 1);
    }

    public function testGetPagerWithEnabledPages()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => true]));
            })
            ->getPager(['enabled' => true], 1);
    }

    public function testGetPagerWithDisabledPages()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => false]));
            })
            ->getPager(['enabled' => false], 1);
    }

    public function testGetPagerWithEditedPages()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.edited = :edited'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['edited' => true]));
            })
            ->getPager(['edited' => true], 1);
    }

    public function testGetPagerWithNonEditedPages()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.edited = :edited'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['edited' => false]));
            })
            ->getPager(['edited' => false], 1);
    }

    public function testGetPagerWithParentChildPages()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('p.parent'),
                    $self->equalTo('pa')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('pa.id = :parentId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['parentId' => 13]));
            })
            ->getPager(['parent' => 13], 1);
    }

    public function testGetPagerWithSitePages()
    {
        $self = $this;
        $this
            ->getPageManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('p.site'),
                    $self->equalTo('s')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.id = :siteId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['siteId' => 13]));
            })
            ->getPager(['site' => 13], 1);
    }

    protected function getPageManager($qbCallback)
    {
        $query = $this->getMockForAbstractClass(AbstractQuery::class, [], '', false, true, true, ['execute']);
        $query->expects($this->any())->method('execute')->willReturn(true);

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$this->createMock(EntityManager::class)])
            ->getMock();

        $qb->expects($this->any())->method('getRootAliases')->willReturn([]);
        $qb->expects($this->any())->method('select')->willReturn($qb);
        $qb->expects($this->any())->method('getQuery')->willReturn($query);

        $qbCallback($qb);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->any())->method('createQueryBuilder')->willReturn($qb);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->any())->method('getFieldNames')->willReturn([
            'name',
            'routeName',
        ]);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->any())->method('getRepository')->willReturn($repository);
        $em->expects($this->any())->method('getClassMetadata')->willReturn($metadata);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())->method('getManagerForClass')->willReturn($em);

        return new PageManager(BasePage::class, $registry);
    }
}
