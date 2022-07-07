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

namespace Sonata\PageBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Backend\RuntimeBackend;
use Sonata\PageBundle\Controller\PageAdminController;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotByPageInterface;

class PageAdminControllerTest extends TestCase
{
    /**
     * @test it is calling the createSnapshot service for "batchActionSnapshot"
     * @group legacy
     */
    public function callTheNotificationCreateSnapshot(): void
    {
        //Mock
        $queryMock = $this->createMock(ProxyQueryInterface::class);
        $pageMock = $this->createMock(PageInterface::class);
        $securityMock = $this->createMock(AbstractAdmin::class);

        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock->method('generateUrl')->willReturn('https://fake.bar');

        $runtimeBackendMock = $this->createMock(BackendInterface::class);
        $runtimeBackendMock
            ->expects(static::once())
            ->method('createAndPublish');

        $pageAdminControllerMock = $this->createPartialMock(PageAdminController::class, ['get']);
        $this->mockAdmin($pageAdminControllerMock, $adminMock);
        $pageAdminControllerMock
            ->method('get')
            ->willReturnOnConsecutiveCalls($securityMock, $runtimeBackendMock);

        $queryMock
            ->method('execute')
            ->willReturn([$pageMock]);

        //Run code
        $pageAdminControllerMock->batchActionSnapshot($queryMock);
    }

    /**
     * @test it is creating snapshot from "batchActionSnapshot"
     */
    public function createSnapshotByPage(): void
    {
        //Mock
        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock->method('generateUrl')->willReturn('https://fake.bar');

        $pageMock = $this->createMock(PageInterface::class);
        $securityMock = $this->createMock(AbstractAdmin::class);
        $runtimeBackendMock = $this->createMock(RuntimeBackend::class);

        $createSnapshotByPageMock = $this->createMock(CreateSnapshotByPageInterface::class);
        $createSnapshotByPageMock
            ->expects(static::once())
            ->method('createByPage');

        $pageAdminControllerMock = $this->createPartialMock(PageAdminController::class, ['get']);
        $this->mockAdmin($pageAdminControllerMock, $adminMock);
        $pageAdminControllerMock
            ->method('get')
            ->willReturnOnConsecutiveCalls($securityMock, $runtimeBackendMock, $createSnapshotByPageMock);

        $queryMock = $this->createMock(ProxyQueryInterface::class);
        $queryMock
            ->method('execute')
            ->willReturn([$pageMock]);

        //Run code
        $pageAdminControllerMock->batchActionSnapshot($queryMock);
    }

    private function mockAdmin(CRUDController $CRUDController, AdminInterface $adminMock): void
    {
        $reflection = new \ReflectionClass($CRUDController);
        $reflectionProperty = $reflection->getProperty('admin');
        $reflectionProperty->setAccessible(true);

        $reflectionProperty->setValue($CRUDController, $adminMock);
    }
}
