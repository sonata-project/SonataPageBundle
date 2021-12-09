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
use Sonata\PageBundle\Entity\BaseSite;
use Sonata\PageBundle\Entity\SiteManager;

final class SiteManagerTest extends TestCase
{
    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPager(): void
    {
        $self = $this;
        $this
            ->getSiteManager(static function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with([]);
                $qb->expects($self->once())->method('orderBy')->with(
                    $self->equalTo('s.name'),
                    $self->equalTo('ASC')
                );
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
            'Invalid sort field \'invalid\' in \'Sonata\\PageBundle\\Entity\\BaseSite\' class'
        );

        $this
            ->getSiteManager(static function ($qb) {
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
            ->getSiteManager(static function ($qb) use ($self) {
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

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithEnabledSites(): void
    {
        $self = $this;
        $this
            ->getSiteManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => true]));
            })
            ->getPager(['enabled' => true], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithDisabledSites(): void
    {
        $self = $this;
        $this
            ->getSiteManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => false]));
            })
            ->getPager(['enabled' => false], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithDefaultSites(): void
    {
        $self = $this;
        $this
            ->getSiteManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.isDefault = :isDefault'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['isDefault' => true]));
            })
            ->getPager(['is_default' => true], 1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPagerWithNonDefaultSites(): void
    {
        $self = $this;
        $this
            ->getSiteManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.isDefault = :isDefault'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['isDefault' => false]));
            })
            ->getPager(['is_default' => false], 1);
    }

    protected function getSiteManager($qbCallback): SiteManager
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
            'host',
        ]);

        $em = $this->createStub(EntityManager::class);
        $em->method('getRepository')->willReturn($repository);
        $em->method('getClassMetadata')->willReturn($metadata);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        return new SiteManager(BaseSite::class, $registry);
    }
}
