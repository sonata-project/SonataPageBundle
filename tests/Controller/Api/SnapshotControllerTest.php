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
 * NEXT_MAJOR: Remove this class.
 *
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 *
 * @group legacy
 */
class SnapshotControllerTest extends TestCase
{
    public function testGetSnapshotsAction(): void
    {
        $snapshotManager = $this->createMock(SnapshotManagerInterface::class);
        $snapshotManager->expects(static::once())->method('getPager')->willReturn([]);

        $paramFetcher = $this->getMockBuilder(ParamFetcherInterface::class)
            ->setMethods(['setController', 'get', 'all'])
            ->getMock();

        $paramFetcher->expects(static::exactly(3))->method('get');
        $paramFetcher->expects(static::once())->method('all')->willReturn([]);

        static::assertSame([], $this->createSnapshotController(null, $snapshotManager)
            ->getSnapshotsAction($paramFetcher));
    }

    public function testGetSnapshotAction(): void
    {
        $snapshot = $this->createMock(SnapshotInterface::class);

        static::assertSame($snapshot, $this->createSnapshotController($snapshot)
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
        $snapshotManager->expects(static::once())->method('delete');

        $view = $this->createSnapshotController($snapshot, $snapshotManager)
            ->deleteSnapshotAction(1);

        static::assertSame(['deleted' => true], $view);
    }

    public function testDeletePageInvalidAction(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $snapshotManager = $this->createMock(SnapshotManagerInterface::class);
        $snapshotManager->expects(static::never())->method('delete');

        $this->createSnapshotController(null, $snapshotManager)
            ->deleteSnapshotAction(1);
    }

    public function createSnapshotController($snapshot = null, $snapshotManager = null): SnapshotController
    {
        if (null === $snapshotManager) {
            $snapshotManager = $this->createMock(SnapshotManagerInterface::class);
        }
        if (null !== $snapshot) {
            $snapshotManager->expects(static::once())->method('findOneBy')->willReturn($snapshot);
        }

        return new SnapshotController($snapshotManager);
    }
}
