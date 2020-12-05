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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BaseSnapshot;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyFactoryInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyInterface;
use Sonata\PageBundle\Model\TransformerInterface;

class SnapshotManagerTest extends TestCase
{
    public function testSetTemplates(): void
    {
        $manager = $this->getMockBuilder(SnapshotManager::class)
            // we need to set at least one method, which does not need to exist!
            // otherwise all methods will be mocked and could not be used!
            // we need the real 'setTemplates' method here!
            ->setMethods([
                'fooBar',
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertSame([], $manager->getTemplates());

        $manager->setTemplates(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $manager->getTemplates());
    }

    public function testGetTemplates(): void
    {
        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->setMethods([
                'setTemplates',
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $managerReflection = new \ReflectionClass($manager);
        $templates = $managerReflection->getProperty('templates');
        $templates->setAccessible(true);
        $templates->setValue($manager, ['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $manager->getTemplates());
    }

    public function testGetTemplate(): void
    {
        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->setMethods([
                'setTemplates',
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $managerReflection = new \ReflectionClass($manager);
        $templates = $managerReflection->getProperty('templates');
        $templates->setAccessible(true);
        $templates->setValue($manager, ['foo' => 'bar']);

        $this->assertSame('bar', $manager->getTemplate('foo'));
    }

    public function testGetTemplatesException(): void
    {
        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->setMethods([
                'setTemplates',
            ])
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('No template references with the code : foo');

        $manager->getTemplate('foo');
    }

    public function testGetPager(): void
    {
        $self = $this;
        $this
            ->getSnapshotManager(static function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with([]);
            })
            ->getPager([], 1);
    }

    public function testGetPagerWithEnabledSnapshots(): void
    {
        $self = $this;
        $this
            ->getSnapshotManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => true]));
            })
            ->getPager(['enabled' => true], 1);
    }

    public function testGetPagerWithDisabledSnapshots(): void
    {
        $self = $this;
        $this
            ->getSnapshotManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['enabled' => false]));
            })
            ->getPager(['enabled' => false], 1);
    }

    public function testGetPagerWithRootSnapshots(): void
    {
        $self = $this;
        $this
            ->getSnapshotManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.parent IS NULL'));
            })
            ->getPager(['root' => true], 1);
    }

    public function testGetPagerWithNonRootSnapshots(): void
    {
        $self = $this;
        $this
            ->getSnapshotManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.parent IS NOT NULL'));
            })
            ->getPager(['root' => false], 1);
    }

    public function testGetPagerWithParentChildSnapshots(): void
    {
        $self = $this;
        $this
            ->getSnapshotManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('s.parent'),
                    $self->equalTo('pa')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('pa.id = :parentId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['parentId' => 13]));
            })
            ->getPager(['parent' => 13], 1);
    }

    public function testGetPagerWithSiteSnapshots(): void
    {
        $self = $this;
        $this
            ->getSnapshotManager(static function ($qb) use ($self) {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('s.site'),
                    $self->equalTo('si')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('si.id = :siteId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(['siteId' => 13]));
            })
            ->getPager(['site' => 13], 1);
    }

    /**
     * Tests the enableSnapshots() method to ensure execute queries are correct.
     */
    public function testEnableSnapshots(): void
    {
        // Given
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('getId')->willReturn(456);

        $snapshot = $this->createMock(Snapshot::class);
        $snapshot->expects($this->once())->method('getId')->willReturn(123);
        $snapshot->expects($this->once())->method('getPage')->willReturn($page);

        $date = new \DateTime();

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('query')
            ->with(sprintf(
                "UPDATE page_snapshot SET publication_date_end = '%s' WHERE id NOT IN(123) AND page_id IN (456) and publication_date_end IS NULL",
                $date->format('Y-m-d H:i:s')
            ));

        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects($this->once())->method('persist')->with($snapshot);
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('getConnection')->willReturn($connection);

        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityManager', 'getTableName'])
            ->getMock();

        $manager->expects($this->exactly(3))->method('getEntityManager')->willReturn($em);
        $manager->expects($this->once())->method('getTableName')->willReturn('page_snapshot');

        // When calling method, expects calls
        $manager->enableSnapshots([$snapshot], $date);
    }

    public function testCreateSnapshotPageProxy(): void
    {
        $proxyInterface = $this->createMock(SnapshotPageProxyInterface::class);

        $snapshotProxyFactory = $this->createMock(SnapshotPageProxyFactoryInterface::class);

        $registry = $this->createMock(ManagerRegistry::class);
        $manager = new SnapshotManager(BaseSnapshot::class, $registry, [], $snapshotProxyFactory);

        $transformer = $this->createMock(TransformerInterface::class);
        $snapshot = $this->createMock(SnapshotInterface::class);

        $snapshotProxyFactory->expects($this->once())->method('create')
            ->with($manager, $transformer, $snapshot)
            ->willReturn($proxyInterface);

        $this->assertSame($proxyInterface, $manager->createSnapshotPageProxy($transformer, $snapshot));
    }

    /**
     * Tests the enableSnapshots() method to ensure execute queries are not executed when no snapshots are given.
     */
    public function testEnableSnapshotsWhenNoSnapshots(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->never())->method('query');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');
        $em->expects($this->never())->method('getConnection');

        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityManager', 'getTableName'])
            ->getMock();

        $manager->expects($this->never())->method('getEntityManager');
        $manager->expects($this->never())->method('getTableName');

        // When calling method, do not expects any calls
        $manager->enableSnapshots([]);
    }

    protected function getSnapshotManager($qbCallback): SnapshotManager
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

        $em = $this->createMock(EntityManager::class);
        $em->method('getRepository')->willReturn($repository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        $snapshotProxyFactory = $this->createMock(SnapshotPageProxyFactoryInterface::class);

        return new SnapshotManager(BaseSnapshot::class, $registry, [], $snapshotProxyFactory);
    }
}
