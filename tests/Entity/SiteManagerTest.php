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

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BaseSite;
use Sonata\PageBundle\Entity\SiteManager;

class SiteManagerTest extends TestCase
{
    public function testGetPager(): void
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self): void {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with([]);
                $qb->expects($self->once())->method('orderBy')->with(
                    $self->equalTo('s.name'),
                    $self->equalTo('ASC')
                );
            })
            ->getPager([], 1);
    }

    public function testGetPagerWithInvalidSort(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid sort field \'invalid\' in \'Sonata\\PageBundle\\Entity\\BaseSite\' class');

        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self): void {
            })
            ->getPager([], 1, 10, ['invalid' => 'ASC']);
    }

    public function testGetPagerWithMultipleSort(): void
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self): void {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with([]);
                $qb->expects($self->exactly(2))->method('orderBy')->with(
                    $self->logicalOr(
                        $self->equalTo('s.name'),
                        $self->equalTo('s.host')
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
                'host' => 'DESC',
            ]);
    }

    public function testGetPagerWithEnabledSites(): void
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => true]));
            })
            ->getPager(['enabled' => true], 1);
    }

    public function testGetPagerWithDisabledSites(): void
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => false]));
            })
            ->getPager(['enabled' => false], 1);
    }

    public function testGetPagerWithDefaultSites(): void
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.isDefault = :isDefault'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['isDefault' => true]));
            })
            ->getPager(['is_default' => true], 1);
    }

    public function testGetPagerWithNonDefaultSites(): void
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self): void {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.isDefault = :isDefault'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['isDefault' => false]));
            })
            ->getPager(['is_default' => false], 1);
    }

    protected function getSiteManager($qbCallback)
    {
        $query = $this->getMockForAbstractClass(AbstractQuery::class, [], '', false, true, true, ['execute']);
        $query->expects($this->any())->method('execute')->will($this->returnValue(true));

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([
                $this->getMockBuilder(EntityManager::class)
                    ->disableOriginalConstructor()
                    ->getMock(),
            ])
            ->getMock();

        $qb->expects($this->any())->method('getRootAliases')->will($this->returnValue([]));
        $qb->expects($this->any())->method('select')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('getQuery')->will($this->returnValue($query));

        $qbCallback($qb);

        $repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $repository->expects($this->any())->method('createQueryBuilder')->will($this->returnValue($qb));

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->any())->method('getFieldNames')->will($this->returnValue([
            'name',
            'host',
        ]));

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->any())->method('getRepository')->will($this->returnValue($repository));
        $em->expects($this->any())->method('getClassMetadata')->will($this->returnValue($metadata));

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())->method('getManagerForClass')->will($this->returnValue($em));

        return new SiteManager(BaseSite::class, $registry);
    }
}
