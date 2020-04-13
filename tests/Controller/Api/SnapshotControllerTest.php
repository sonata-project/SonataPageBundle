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

namespace Sonata\PageBundle\Tests\Controller\Api;

use FOS\RestBundle\Request\ParamFetcherInterface;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Controller\Api\SnapshotController;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 */
class SnapshotControllerTest extends TestCase
{
    public function testGetSnapshotsAction(): void
    {
        $snapshotManager = $this->createMock(SnapshotManagerInterface::class);
        $snapshotManager->expects($this->once())->method('getPager')->willReturn([]);

        $paramFetcher = $this->getMockBuilder(ParamFetcherInterface::class)
            ->setMethods(['setController', 'get', 'all'])
            ->getMock();

        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->willReturn([]);

        $this->assertSame([], $this->createSnapshotController(null, $snapshotManager)
            ->getSnapshotsAction($paramFetcher));
    }

    public function testGetSnapshotAction(): void
    {
        $snapshot = $this->createMock(SnapshotInterface::class);

        $this->assertSame($snapshot, $this->createSnapshotController($snapshot)
            ->getSnapshotAction(1));
    }

    public function testGetSnapshotActionNotFoundException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Snapshot (1) not found');

        $this->createSnapshotController()->getSnapshotAction(1);
    }

    public function testDeleteSnapshotAction(): void
    {
        $snapshot = $this->createMock(SnapshotInterface::class);

        $snapshotManager = $this->createMock(SnapshotManagerInterface::class);
        $snapshotManager->expects($this->once())->method('delete');

        $view = $this->createSnapshotController($snapshot, $snapshotManager)
            ->deleteSnapshotAction(1);

        $this->assertSame(['deleted' => true], $view);
    }

    public function testDeletePageInvalidAction(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $snapshotManager = $this->createMock(SnapshotManagerInterface::class);
        $snapshotManager->expects($this->never())->method('delete');

        $this->createSnapshotController(null, $snapshotManager)
            ->deleteSnapshotAction(1);
    }

    public function createSnapshotController($snapshot = null, $snapshotManager = null): SnapshotController
    {
        if (null === $snapshotManager) {
            $snapshotManager = $this->createMock(SnapshotManagerInterface::class);
        }
        if (null !== $snapshot) {
            $snapshotManager->expects($this->once())->method('findOneBy')->willReturn($snapshot);
        }

        return new SnapshotController($snapshotManager);
    }
}
