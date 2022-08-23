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
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Service\CreateSnapshotService;

final class CreateSnapshotServiceTest extends TestCase
{
    public function testCreateBySite(): void
    {
        // Mocks
        $snapshotManager = $this->createMock(SnapshotManagerInterface::class);

        $pageManager = $this->createMock(PageManagerInterface::class);
        $pageManager
            ->method('findBy')
            ->willReturn([$this->createMock(PageInterface::class)]);

        $transformer = $this->createMock(TransformerInterface::class);
        $transformer
            ->method('create')
            ->willReturn($this->createMock(SnapshotInterface::class));

        $site = $this->createMock(SiteInterface::class);

        // Asserts mocks
        $transformer
            ->expects(static::once())
            ->method('create');

        // Execute code
        $createSnapshotService = new CreateSnapshotService(
            $snapshotManager,
            $pageManager,
            $transformer
        );
        $createSnapshotService->createBySite($site);
    }
}
