<?php
namespace Sonata\PageBundle\Tests\Entity;

use Sonata\PageBundle\Entity\SnapshotManager;

class SnapshotManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFindEnabledSnapshot()
    {
        $entityManager = $this->getMock(
            'Doctrine\Common\Persistence\ManagerRegistry',
            array(),
            array(),
            '',
            false);

        $manager = new SnapshotManager(
            'Sonata\PageBundle\Entity\Snapshot',
            $entityManager
        );

        $manager->findEnableSnapshot(array('pageId' => false));
    }
}
