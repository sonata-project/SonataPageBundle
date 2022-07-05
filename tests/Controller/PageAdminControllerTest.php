<?php

namespace Sonata\PageBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
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
            ->expects($this->once())
            ->method('createAndPublish');

        $pageAdminControllerMock = $this->createPartialMock(PageAdminController::class, ['get', 'getAdmin']);
        $pageAdminControllerMock
            ->method('get')
            ->willReturnOnConsecutiveCalls($securityMock, $runtimeBackendMock);

        $pageAdminControllerMock
            ->method('getAdmin')
            ->willReturn($adminMock);

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
        $pageMock = $this->createMock(PageInterface::class);
        $securityMock = $this->createMock(AbstractAdmin::class);
        $runtimeBackendMock = $this->createMock(RuntimeBackend::class);

        $createSnapshotByPageMock = $this->createMock(CreateSnapshotByPageInterface::class);
        $createSnapshotByPageMock
            ->expects($this->once())
            ->method('createByPage');

        $adminMock = $this->createMock(AdminInterface::class);
        $adminMock->method('generateUrl')->willReturn('https://fake.bar');

        $pageAdminControllerMock = $this->createPartialMock(PageAdminController::class, ['get', 'getAdmin']);
        $pageAdminControllerMock
            ->method('get')
            ->willReturnOnConsecutiveCalls($securityMock, $runtimeBackendMock, $createSnapshotByPageMock);
        $pageAdminControllerMock
            ->method('getAdmin')
            ->willReturn($adminMock);

        $queryMock = $this->createMock(ProxyQueryInterface::class);
        $queryMock
            ->method('execute')
            ->willReturn([$pageMock]);

        //Run code
        $pageAdminControllerMock->batchActionSnapshot($queryMock);
    }
}