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

namespace Sonata\PageBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Service\CleanupSnapshotService;

class CleanupSnapshotServiceTest extends TestCase
{
    /**
     * @testdox it is calling the code that clean up snapshots into the database.
     */
    public function testCallCleanupQuery(): void
    {
        // Mock
        $snapshotManagerMock = $this->createMock(SnapshotManager::class);
        $snapshotManagerMock
            ->method('getEntityManager')
            ->willReturn($this->createMock(EntityManagerInterface::class));

        $snapshotManagerMock
            ->expects(static::once())
            ->method('cleanup')
            ->with(static::isInstanceOf(PageInterface::class), static::equalTo(4));

        $pageManagerMock = $this->createMock(PageManagerInterface::class);
        $pageManagerMock
            ->expects(static::once())
            ->method('findBy')
            ->with(['site' => 2])
            ->willReturn([$this->createMock(PageInterface::class)]);

        $siteMock = $this->createMock(Site::class);
        $siteMock
            ->method('getId')
            ->willReturn(2);

        // Run code
        $cleanupSnapshot = new CleanupSnapshotService($snapshotManagerMock, $pageManagerMock);
        $cleanupSnapshot->cleanupBySite($siteMock, 4);
    }
}
