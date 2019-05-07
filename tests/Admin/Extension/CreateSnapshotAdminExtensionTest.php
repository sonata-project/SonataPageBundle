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

class CreateSnapshotAdminExtensionTest extends TestCase
{
    public function testPostUpdateOnPage()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('getId')->willReturn(42);

        $admin = $this->createMock(AdminInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects($this->once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            ['pageId' => 42]
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postUpdate($admin, $page);
    }

    public function testPostPersistOnPage()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('getId')->willReturn(42);

        $admin = $this->createMock(AdminInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects($this->once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            ['pageId' => 42]
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postPersist($admin, $page);
    }

    public function testPostUpdateOnBlock()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('getId')->willReturn(42);

        $block = $this->createMock(PageBlockInterface::class);
        $block->expects($this->once())->method('getPage')->willReturn($page);

        $admin = $this->createMock(AdminInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects($this->once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            ['pageId' => 42]
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postUpdate($admin, $block);
    }

    public function testPostPersistOnBlock()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('getId')->willReturn(42);

        $block = $this->createMock(PageBlockInterface::class);
        $block->expects($this->once())->method('getPage')->willReturn($page);

        $admin = $this->createMock(AdminInterface::class);

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects($this->once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            ['pageId' => 42]
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postPersist($admin, $block);
    }
}
