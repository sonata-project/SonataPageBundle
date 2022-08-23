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

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Service\CleanupSnapshotService;

class CleanupSnapshotServiceTest extends TestCase
{
    public function testCallCleanupQuery(): void
    {
        // Mock
        $snapshotManager = $this->createMock(SnapshotManagerInterface::class);

        $snapshotManager
            ->expects(static::once())
            ->method('cleanup')
            ->with(static::isInstanceOf(PageInterface::class), static::equalTo(4));

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager
            ->expects(static::once())
            ->method('findBy')
            ->with(['site' => 2])
            ->willReturn([$this->createMock(PageInterface::class)]);

        $site = $this->createMock(Site::class);
        $site
            ->method('getId')
            ->willReturn(2);

        // Run code
        $cleanupSnapshot = new CleanupSnapshotService($snapshotManager, $pageManager);
        $cleanupSnapshot->cleanupBySite($site, 4);
    }
}
