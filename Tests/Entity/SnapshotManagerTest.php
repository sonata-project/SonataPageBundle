<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Entity;

use Sonata\PageBundle\Entity\SnapshotManager;

/**
 * Class SnapshotManagerTest
 *
 */
class SnapshotManagerTest extends \PHPUnit_Framework_TestCase
{
    protected function getSnapshotManager($qbCallback)
    {
        $query = $this->getMockForAbstractClass('Doctrine\ORM\AbstractQuery', array(), '', false, true, true, array('execute'));
        $query->expects($this->any())->method('execute')->will($this->returnValue(true));


        $qb = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(
            $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock()
        ));

        $qb->expects($this->any())->method('select')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('getQuery')->will($this->returnValue($query));

        $qbCallback($qb);

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repository->expects($this->any())->method('createQueryBuilder')->will($this->returnValue($qb));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $em->expects($this->any())->method('getRepository')->will($this->returnValue($repository));

        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())->method('getManagerForClass')->will($this->returnValue($em));

        return new SnapshotManager('Sonata\PageBundle\Entity\BaseSnapshot', $registry);
    }

    public function testGetPager()
    {
        $self = $this;
        $this
            ->getSnapshotManager(function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with(array());
            })
            ->getPager(array(), 1);
    }

    public function testGetPagerWithEnabledSnapshots()
    {
        $self = $this;
        $this
            ->getSnapshotManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('enabled' => true)));
            })
            ->getPager(array('enabled' => true), 1);
    }

    public function testGetPagerWithDisabledSnapshots()
    {
        $self = $this;
        $this
            ->getSnapshotManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('enabled' => false)));
            })
            ->getPager(array('enabled' => false), 1);
    }

    public function testGetPagerWithRootSnapshots()
    {
        $self = $this;
        $this
            ->getSnapshotManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.parent IS NULL'));
            })
            ->getPager(array('root' => true), 1);
    }

    public function testGetPagerWithNonRootSnapshots()
    {
        $self = $this;
        $this
            ->getSnapshotManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.parent IS NOT NULL'));
            })
            ->getPager(array('root' => false), 1);
    }

    public function testGetPagerWithParentChildSnapshots()
    {
        $self = $this;
        $this
            ->getSnapshotManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('s.parent'),
                    $self->equalTo('pa')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('pa.id = :parentId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('parentId' => 13)));
            })
            ->getPager(array('parent' => 13), 1);
    }

    public function testGetPagerWithSiteSnapshots()
    {
        $self = $this;
        $this
            ->getSnapshotManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('s.site'),
                    $self->equalTo('si')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('si.id = :siteId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('siteId' => 13)));
            })
            ->getPager(array('site' => 13), 1);
    }

    /**
     * Tests the enableSnapshots() method to ensure execute queries are correct
     */
    public function testEnableSnapshots()
    {
        // Given
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getId')->will($this->returnValue(456));

        $snapshot = $this->getMock('Sonata\PageBundle\Tests\Entity\Snapshot');
        $snapshot->expects($this->once())->method('getId')->will($this->returnValue(123));
        $snapshot->expects($this->once())->method('getPage')->will($this->returnValue($page));

        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $date = new \DateTime();

        $connection
            ->expects($this->once())
            ->method('query')
            ->with(sprintf("UPDATE page_snapshot SET publication_date_end = '%s' WHERE id NOT IN(123) AND page_id IN (456)", $date->format('Y-m-d H:i:s')));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())->method('persist')->with($snapshot);
        $em->expects($this->once())->method('flush');
        $em->expects($this->once())->method('getConnection')->will($this->returnValue($connection));

        $manager  = $this->getMockBuilder('Sonata\PageBundle\Entity\SnapshotManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getEntityManager', 'getTableName'))
            ->getMock();

        $manager->expects($this->exactly(3))->method('getEntityManager')->will($this->returnValue($em));
        $manager->expects($this->once())->method('getTableName')->will($this->returnValue('page_snapshot'));

        // When calling method, expects calls
        $manager->enableSnapshots(array($snapshot), $date);
    }

    /**
     * Tests the enableSnapshots() method to ensure execute queries are not executed when no snapshots are given
     */
    public function testEnableSnapshotsWhenNoSnapshots()
    {
        // Given
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->never())->method('query');

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->never())->method('persist');
        $em->expects($this->never())->method('flush');
        $em->expects($this->never())->method('getConnection');

        $manager  = $this->getMockBuilder('Sonata\PageBundle\Entity\SnapshotManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getEntityManager', 'getTableName'))
            ->getMock();

        $manager->expects($this->never())->method('getEntityManager');
        $manager->expects($this->never())->method('getTableName');

        // When calling method, do not expects any calls
        $manager->enableSnapshots(array());
    }
}
