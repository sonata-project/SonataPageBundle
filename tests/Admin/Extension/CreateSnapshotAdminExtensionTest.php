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
use Sonata\PageBundle\Admin\Extension\CreateSnapshotAdminExtension;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotByPageInterface;

final class CreateSnapshotAdminExtensionTest extends TestCase
{
    public function testPostUpdateOnPage(): void
    {
        $page = $this->createMock(PageInterface::class);
        $admin = $this->createMock(AdminInterface::class);
        $createSnapshotByPage = $this->createMock(CreateSnapshotByPageInterface::class);

        $createSnapshotByPage->expects(static::once())->method('createByPage')->with($page);

        $extension = new CreateSnapshotAdminExtension($createSnapshotByPage);
        $extension->postUpdate($admin, $page);
    }

    public function testPostPersistOnPage(): void
    {
        $page = $this->createMock(PageInterface::class);
        $admin = $this->createMock(AdminInterface::class);
        $createSnapshotByPage = $this->createMock(CreateSnapshotByPageInterface::class);

        $createSnapshotByPage->expects(static::once())->method('createByPage')->with($page);

        $extension = new CreateSnapshotAdminExtension($createSnapshotByPage);
        $extension->postPersist($admin, $page);
    }

    public function testPostUpdateOnBlock(): void
    {
        $page = $this->createMock(PageInterface::class);
        $block = $this->createMock(PageBlockInterface::class);
        $admin = $this->createMock(AdminInterface::class);
        $createSnapshotByPage = $this->createMock(CreateSnapshotByPageInterface::class);

        $block->expects(static::once())->method('getPage')->willReturn($page);
        $createSnapshotByPage->expects(static::once())->method('createByPage')->with($page);

        $extension = new CreateSnapshotAdminExtension($createSnapshotByPage);
        $extension->postUpdate($admin, $block);
    }

    public function testPostPersistOnBlock(): void
    {
        $page = $this->createMock(PageInterface::class);
        $block = $this->createMock(PageBlockInterface::class);
        $admin = $this->createMock(AdminInterface::class);
        $createSnapshotByPage = $this->createMock(CreateSnapshotByPageInterface::class);

        $block->expects(static::once())->method('getPage')->willReturn($page);
        $createSnapshotByPage->expects(static::once())->method('createByPage')->with($page);

        $extension = new CreateSnapshotAdminExtension($createSnapshotByPage);
        $extension->postPersist($admin, $block);
    }

    public function testPostRemoveOnBlock(): void
    {
        $page = $this->createMock(PageInterface::class);
        $block = $this->createMock(PageBlockInterface::class);
        $admin = $this->createStub(AdminInterface::class);
        $createSnapshotByPage = $this->createMock(CreateSnapshotByPageInterface::class);

        $block->expects(static::once())->method('getPage')->willReturn($page);
        $createSnapshotByPage->expects(static::once())->method('createByPage')->with($page);

        $extension = new CreateSnapshotAdminExtension($createSnapshotByPage);
        $extension->postRemove($admin, $block);
    }

    public function testCreateSnapshotByPage(): void
    {
        $admin = $this->createMock(AdminInterface::class);
        $page = $this->createMock(PageInterface::class);
        $createSnapshotByPage = $this->createMock(CreateSnapshotByPageInterface::class);

        $createSnapshotByPage->expects(static::once())->method('createByPage')->with($page);

        $createSnapshotAdminExtension = new CreateSnapshotAdminExtension($createSnapshotByPage);
        $createSnapshotAdminExtension->postUpdate($admin, $page);
    }
}
