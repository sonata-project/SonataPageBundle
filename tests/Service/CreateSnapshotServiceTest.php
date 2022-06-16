<?php

namespace Sonata\PageBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Service\CreateSnapshotService;

final class CreateSnapshotServiceTest extends TestCase
{
    /**
     * @test
     * @testdox it is executing the code inside of code
     */
    public function createBySite(): void
    {
        //Mocks
        $snapshotManagerMock = $this->createMock(SnapshotManager::class);
        $snapshotManagerMock
            ->method('getEntityManager')
            ->willReturn($this->createMock(EntityManagerInterface::class));

        $pageManagerMock = $this->createMock(PageManagerInterface::class);
        $pageManagerMock
            ->method('findBy')
            ->willReturn([$this->createMock(PageInterface::class)]);

        $transformerMock = $this->createMock(TransformerInterface::class);
        $siteMock = $this->createMock(SiteInterface::class);

        //Asserts mocks
        $transformerMock
            ->expects($this->once())
            ->method('create');


        //Execute code
        $createSnapshotService = new CreateSnapshotService(
            $snapshotManagerMock,
            $pageManagerMock,
            $transformerMock
        );
        $createSnapshotService->createBySite($siteMock);
    }
}