<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Controller\Api;

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Controller\Api\SnapshotController;

/**
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 */
class SnapshotControllerTest extends TestCase
{
    public function testGetSnapshotsAction()
    {
        $snapshotManager = $this->getMockBuilder('Sonata\PageBundle\Model\SnapshotManagerInterface')->getMock();
        $snapshotManager->expects($this->once())->method('getPager')->will($this->returnValue([]));

        $paramFetcher = $this->getMockBuilder('FOS\RestBundle\Request\ParamFetcherInterface')
            ->setMethods(['addParam', 'setController', 'get', 'all'])
            ->getMock();

        $paramFetcher->expects($this->once())->method('addParam');
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue([]));

        $this->assertEquals([], $this->createSnapshotController(null, $snapshotManager)->getSnapshotsAction($paramFetcher));
    }

    public function testGetSnapshotAction()
    {
        $snapshot = $this->createMock('Sonata\PageBundle\Model\SnapshotInterface');

        $this->assertEquals($snapshot, $this->createSnapshotController($snapshot)->getSnapshotAction(1));
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Snapshot (1) not found
     */
    public function testGetSnapshotActionNotFoundException()
    {
        $this->createSnapshotController()->getSnapshotAction(1);
    }

    public function testDeleteSnapshotAction()
    {
        $snapshot = $this->createMock('Sonata\PageBundle\Model\SnapshotInterface');

        $snapshotManager = $this->createMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        $snapshotManager->expects($this->once())->method('delete');

        $view = $this->createSnapshotController($snapshot, $snapshotManager)->deleteSnapshotAction(1);

        $this->assertEquals(['deleted' => true], $view);
    }

    public function testDeletePageInvalidAction()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $snapshotManager = $this->createMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        $snapshotManager->expects($this->never())->method('delete');

        $this->createSnapshotController(null, $snapshotManager)->deleteSnapshotAction(1);
    }

    /**
     * @param $snapshot
     * @param $snapshotManager
     *
     * @return SnapshotController
     */
    public function createSnapshotController($snapshot = null, $snapshotManager = null)
    {
        if (null === $snapshotManager) {
            $snapshotManager = $this->createMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        }
        if (null !== $snapshot) {
            $snapshotManager->expects($this->once())->method('findOneBy')->will($this->returnValue($snapshot));
        }

        return new SnapshotController($snapshotManager);
    }
}
