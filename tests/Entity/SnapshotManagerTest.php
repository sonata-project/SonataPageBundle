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
use Doctrine\ORM\Configuration;
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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BaseSnapshot;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Model\SnapshotPageProxyFactory;
use Sonata\PageBundle\Model\SnapshotPageProxyFactoryInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;
use Sonata\PageBundle\Tests\Model\Page;

final class SnapshotManagerTest extends TestCase
{
    /**
     * @var Stub&ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var MockObject&EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SnapshotManager
     */
    private $manager;

    protected function setUp(): void
    {
        $this->managerRegistry = $this->createStub(ManagerRegistry::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->managerRegistry->method('getManagerForClass')->willReturn($this->entityManager);

        $this->manager = new SnapshotManager(
            BaseSnapshot::class,
            $this->managerRegistry,
            new SnapshotPageProxyFactory(SnapshotPageProxy::class),
            []
        );
    }

    public function testSetTemplates(): void
    {
        static::assertSame([], $this->manager->getTemplates());

        $this->manager->setTemplates(['foo' => 'bar']);

        static::assertSame(['foo' => 'bar'], $this->manager->getTemplates());
    }

    public function testGetTemplate(): void
    {
        $this->manager->setTemplates(['foo' => 'bar']);

        static::assertSame('bar', $this->manager->getTemplate('foo'));
    }

    public function testGetTemplatesException(): void
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('No template references with the code : foo');

        $this->manager->getTemplate('foo');
    }

    /**
     * Tests the enableSnapshots() method to ensure execute queries are correct.
     */
    public function testEnableSnapshots(): void
    {
        $platform = $this->createMock(AbstractPlatform::class);
        $connection = $this->createMock(Connection::class);
        $unit = $this->createMock(UnitOfWork::class);
        $metaDataFactory = $this->createMock(ClassMetadataFactory::class);

        $page = new Page();
        $page->setId(456);

        $snapshot = new SonataPageSnapshot();
        $snapshot->setId(123);
        $snapshot->setPage($page);

        $date = new \DateTime();

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

        $connection->expects(static::once())->method('executeStatement')->with(
            'UPDATE page_snapshot SET publication_date_end = ? WHERE id NOT IN (123) AND page_id IN (456) AND publication_date_end IS NULL',
            [$date],
            ['datetime']
        )->willReturn(0);

        $platform->method('getDateTimeFormatString')->willReturn('Y-m-d H:i:s');
        $connection->method('getDatabasePlatform')->willReturn($platform);
        $connection->method('getParams')->willReturn([]);
        $unit->method('getSingleIdentifierValue')->willReturnCallback(static function ($entity) {
            if ($entity instanceof \DateTime) {
                throw new MappingException();
            }

            return null;
        });

        $metaDataFactory->method('hasMetadataFor')
            ->willReturnCallback(static function ($class) {
                return SonataPageSnapshot::class === $class;
            });
        $metaDataFactory->method('getMetadataFor')->with(SonataPageSnapshot::class)->willReturn($classMetadata);

        $this->entityManager->expects(static::once())->method('persist')->with($snapshot);
        $this->entityManager->expects(static::once())->method('flush');
        $this->entityManager->expects(static::atLeastOnce())->method('getConnection')->willReturn($connection);
        $this->entityManager->expects(static::once())->method('createQueryBuilder')->willReturn(new QueryBuilder($this->entityManager));
        $this->entityManager->method('getConfiguration')->willReturn(new Configuration());
        $this->entityManager->method('getExpressionBuilder')->willReturn(new Expr());
        $this->entityManager->expects(static::once())->method('createQuery')->willReturnCallback(function ($dql) {
            $query = new Query($this->entityManager);
            $query->setDQL($dql);

            return $query;
        });
        $this->entityManager->method('getUnitOfWork')->willReturn($unit);
        $this->entityManager->method('getClassMetadata')->with(SonataPageSnapshot::class)->willReturn($classMetadata);
        $this->entityManager->method('getMetadataFactory')->willReturn($metaDataFactory);
        $this->entityManager->method('getRepository')->willReturn(new EntityRepository($this->entityManager, $classMetadata));

        // When calling method, expects calls
        $this->manager->enableSnapshots([$snapshot], $date);
    }

    public function testCreateSnapshotPageProxy(): void
    {
        $proxyInterface = $this->createMock(SnapshotPageProxyInterface::class);
        $snapshotProxyFactory = $this->createMock(SnapshotPageProxyFactoryInterface::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $transformer = $this->createMock(TransformerInterface::class);
        $snapshot = $this->createMock(SnapshotInterface::class);

        $manager = new SnapshotManager(BaseSnapshot::class, $registry, $snapshotProxyFactory, []);

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

        $this->entityManager->expects(static::never())->method('persist');
        $this->entityManager->expects(static::never())->method('flush');
        $this->entityManager->expects(static::never())->method('getConnection');

        // When calling method, do not expects any calls
        $this->manager->enableSnapshots([]);
    }
}
