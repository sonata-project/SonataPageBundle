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

namespace Sonata\PageBundle\Tests\Admin\Extension;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\Admin\Extension\CreateSnapshotAdminExtension;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotByPageInterface;

final class CreateSnapshotAdminExtensionTest extends TestCase
{
    public function testPostUpdateOnPage(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getId')->willReturn(42);

        $admin = $this->createMock(AdminInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects(static::once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            ['pageId' => 42]
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postUpdate($admin, $page);
    }

    public function testPostPersistOnPage(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getId')->willReturn(42);

        $admin = $this->createMock(AdminInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects(static::once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            ['pageId' => 42]
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postPersist($admin, $page);
    }

    public function testPostUpdateOnBlock(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getId')->willReturn(42);

        $block = $this->createMock(PageBlockInterface::class);
        $block->expects(static::once())->method('getPage')->willReturn($page);

        $admin = $this->createMock(AdminInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects(static::once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            ['pageId' => 42]
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postUpdate($admin, $block);
    }

    public function testPostPersistOnBlock(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getId')->willReturn(42);

        $block = $this->createMock(PageBlockInterface::class);
        $block->expects(static::once())->method('getPage')->willReturn($page);

        $admin = $this->createMock(AdminInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects(static::once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            ['pageId' => 42]
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postPersist($admin, $block);
    }

    public function testPostRemoveOnBlock(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('getId')->willReturn(42);

        $block = $this->createMock(PageBlockInterface::class);
        $block->expects(static::once())->method('getPage')->willReturn($page);

        $admin = $this->createStub(AdminInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects(static::once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            ['pageId' => 42]
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postRemove($admin, $block);
    }

    /**
     * @test
     * @testdox it's creating snapshot by page object
     */
    public function createSnapshotByPage(): void
    {
        // Mocks
        $adminMock = $this->createMock(AdminInterface::class);

        $pageMock = $this->createMock(PageInterface::class);

        $createSnapshotByPageMock = $this->createMock(CreateSnapshotByPageInterface::class);
        $createSnapshotByPageMock
            ->expects(static::once())
            ->method('createByPage')
            ->with(static::isInstanceOf(PageInterface::class));

        // Run code
        $createSnapshotAdminExtension = new CreateSnapshotAdminExtension($createSnapshotByPageMock);
        $createSnapshotAdminExtension->postUpdate($adminMock, $pageMock);
    }
}
