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
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\MappingException;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BaseSnapshot;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyFactoryInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;

final class SnapshotManagerTest extends TestCase
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
            ->getMock();

        static::assertSame([], $manager->getTemplates());

        $manager->setTemplates(['foo' => 'bar']);

        static::assertSame(['foo' => 'bar'], $manager->getTemplates());
    }

    public function testGetTemplates(): void
    {
        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->setMethods([
                'setTemplates',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $managerReflection = new \ReflectionClass($manager);
        $templates = $managerReflection->getProperty('templates');
        $templates->setAccessible(true);
        $templates->setValue($manager, ['foo' => 'bar']);

        static::assertSame(['foo' => 'bar'], $manager->getTemplates());
    }

    public function testGetTemplate(): void
    {
        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->setMethods([
                'setTemplates',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $managerReflection = new \ReflectionClass($manager);
        $templates = $managerReflection->getProperty('templates');
        $templates->setAccessible(true);
        $templates->setValue($manager, ['foo' => 'bar']);

        static::assertSame('bar', $manager->getTemplate('foo'));
    }

    public function testGetTemplatesException(): void
    {
        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->setMethods([
                'setTemplates',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No template references with the code : foo');

        $manager->getTemplate('foo');
    }

    /**
     * Tests the enableSnapshots() method to ensure execute queries are correct.
     */
    public function testEnableSnapshots(): void
    {
        // Given
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getId')->willReturn(456);

        $snapshot = $this->getMockBuilder(SnapshotInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMockForAbstractClass();
        $snapshot->expects(static::once())->method('getId')->willReturn(123);
        $snapshot->expects(static::once())->method('getPage')->willReturn($page);

        $date = new \DateTime();

        $connection = $this->createMock(Connection::class);

        $sql = 'UPDATE page_snapshot SET publication_date_end = ? WHERE id NOT IN (123) AND page_id IN (456) AND publication_date_end IS NULL';

        $connection->expects(static::once())->method('executeStatement')->with($sql, [$date], ['datetime'])->willReturn(0);

        $platform = $this->createMock(AbstractPlatform::class);
        $platform->method('getDateTimeFormatString')->willReturn('Y-m-d H:i:s');
        $connection->method('getDatabasePlatform')->willReturn($platform);
        $connection->method('getParams')->willReturn([]);

        $em = $this->createMock(EntityManagerInterface::class);
        $config = new Configuration();

        $qb = new QueryBuilder($em);
        $expr = new Expr();
        $em->expects(static::once())->method('persist')->with($snapshot);
        $em->expects(static::once())->method('flush');
        $em->expects(static::atLeastOnce())->method('getConnection')->willReturn($connection);
        $em->expects(static::once())->method('createQueryBuilder')->willReturn($qb);
        $em->method('getConfiguration')->willReturn($config);
        $em->method('getExpressionBuilder')->willReturn($expr);
        $em->expects(static::once())->method('createQuery')->willReturnCallback(static function ($dql) use ($em) {
            $query = new Query($em);
            $query->setDQL($dql);

            return $query;
        });

        $unit = $this->createMock(UnitOfWork::class);
        $unit->method('getSingleIdentifierValue')->willReturnCallback(static function ($entity) {
            if ($entity instanceof \DateTime) {
                throw new MappingException();
            }

            return null;
        });
        $em->method('getUnitOfWork')->willReturn($unit);

        $classMetadata = new ClassMetadata(SonataPageSnapshot::class);
        $classMetadata->setPrimaryTable([
            'name' => 'page_snapshot',
        ]);
        $classMetadata->addInheritedFieldMapping([
            'fieldName' => 'publicationDateEnd',
            'columnName' => 'publication_date_end',
            'type' => 'datetime',
            'nullable' => true,
        ]);
        $classMetadata->addInheritedFieldMapping([
            'fieldName' => 'id',
            'columnName' => 'id',
        ]);
        $classMetadata->addInheritedFieldMapping([
            'fieldName' => 'page',
            'columnName' => 'page_id',
        ]);
        $classMetadata->identifier = ['id'];

        $em->method('getClassMetadata')->with(SonataPageSnapshot::class)->willReturn($classMetadata);

        $metaDataFactory = $this->createMock(ClassMetadataFactory::class);
        $metaDataFactory->method('hasMetadataFor')
            ->willReturnCallback(static fn ($class) => SonataPageSnapshot::class === $class);
        $metaDataFactory->method('getMetadataFor')->with(SonataPageSnapshot::class)->willReturn($classMetadata);
        $em->method('getMetadataFactory')->willReturn($metaDataFactory);

        $repo = new EntityRepository($em, $classMetadata);

        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityManager', 'getRepository'])
            ->getMock();

        $manager->expects(static::exactly(2))->method('getEntityManager')->willReturn($em);
        $manager->method('getRepository')->willReturn($repo);

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

        $snapshotProxyFactory->expects(static::once())->method('create')
            ->with($manager, $transformer, $snapshot)
            ->willReturn($proxyInterface);

        static::assertSame($proxyInterface, $manager->createSnapshotPageProxy($transformer, $snapshot));
    }

    /**
     * Tests the enableSnapshots() method to ensure execute queries are not executed when no snapshots are given.
     */
    public function testEnableSnapshotsWhenNoSnapshots(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(static::never())->method('query');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(static::never())->method('persist');
        $em->expects(static::never())->method('flush');
        $em->expects(static::never())->method('getConnection');

        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityManager', 'getTableName'])
            ->getMock();

        $manager->expects(static::never())->method('getEntityManager');
        $manager->expects(static::never())->method('getTableName');

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
