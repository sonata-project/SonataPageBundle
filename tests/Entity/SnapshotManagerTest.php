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
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BaseSnapshot;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyFactoryInterface;
use Sonata\PageBundle\Model\SnapshotPageProxyInterface;
use Sonata\PageBundle\Model\TransformerInterface;

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

        $this->expectException('RuntimeException');
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
        $connection
            ->expects(static::once())
            ->method('executeQuery')
            ->with(sprintf(
                "UPDATE page_snapshot SET publication_date_end = '%s' WHERE id NOT IN(123) AND page_id IN (456) and publication_date_end IS NULL",
                $date->format('Y-m-d H:i:s')
            ));

        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects(static::once())->method('persist')->with($snapshot);
        $em->expects(static::once())->method('flush');
        $em->expects(static::once())->method('getConnection')->willReturn($connection);

        $manager = $this->getMockBuilder(SnapshotManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityManager', 'getTableName'])
            ->getMock();

        $manager->expects(static::exactly(3))->method('getEntityManager')->willReturn($em);
        $manager->expects(static::once())->method('getTableName')->willReturn('page_snapshot');

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
        $connection->expects(static::never())->method('executeQuery');

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
}
