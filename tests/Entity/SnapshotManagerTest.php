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
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BaseSnapshot;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Model\SnapshotPageProxyFactory;
use Sonata\PageBundle\Model\SnapshotPageProxyFactoryInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyInterface;
use Sonata\PageBundle\Model\TransformerInterface;

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
            [],
            new SnapshotPageProxyFactory(SnapshotPageProxy::class)
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
        $connection
            ->expects(static::once())
            ->method('query')
            ->with(sprintf(
                "UPDATE page_snapshot SET publication_date_end = '%s' WHERE id NOT IN(123) AND page_id IN (456) and publication_date_end IS NULL",
                $date->format('Y-m-d H:i:s')
            ));

        $metadata = new ClassMetadataInfo(BaseSnapshot::class);
        $metadata->table['name'] = 'page_snapshot';

        $this->entityManager->expects(static::once())->method('getClassMetadata')->willReturn($metadata);
        $this->entityManager->expects(static::once())->method('persist')->with($snapshot);
        $this->entityManager->expects(static::once())->method('flush');
        $this->entityManager->expects(static::once())->method('getConnection')->willReturn($connection);

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

        $manager = new SnapshotManager(BaseSnapshot::class, $registry, [], $snapshotProxyFactory);

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
