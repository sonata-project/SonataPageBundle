<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\Test\PageBundle\Controller\Api;

use Sonata\PageBundle\Controller\Api\SnapshotController;

/**
 * Class SnapshotControllerTest
 *
 * @package Sonata\Test\PageBundle\Controller\Api
 *
 * @author Benoit de Jacobet <benoit.de-jacobet@ekino.com>
 */
class SnapshotControllerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetSnapshotsAction()
    {
        $snapshotManager = $this->getMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        $snapshotManager->expects($this->once())->method('getPager')->will($this->returnValue(array()));

        $paramFetcher = $this->getMock('FOS\RestBundle\Request\ParamFetcherInterface');
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->will($this->returnValue(array()));

        $this->assertEquals(array(), $this->createSnapshotController(null, $snapshotManager)->getSnapshotsAction($paramFetcher));
    }

    public function testGetSnapshotAction()
    {
        $snapshot = $this->getMock('Sonata\PageBundle\Model\SnapshotInterface');

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
        $snapshot = $this->getMock('Sonata\PageBundle\Model\SnapshotInterface');

        $snapshotManager = $this->getMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        $snapshotManager->expects($this->once())->method('delete');

        $view = $this->createSnapshotController($snapshot, $snapshotManager)->deleteSnapshotAction(1);

        $this->assertEquals(array('deleted' => true), $view);
    }

    public function testDeletePageInvalidAction()
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $snapshotManager = $this->getMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
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
            $snapshotManager = $this->getMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        }
        if (null !== $snapshot) {
            $snapshotManager->expects($this->once())->method('findOneBy')->will($this->returnValue($snapshot));
        }

        return new SnapshotController($snapshotManager);
    }
}
