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

use Sonata\PageBundle\Entity\SiteManager;

/**
 * Class SiteManagerTest
 *
 */
class SiteManagerTest extends \PHPUnit_Framework_TestCase
{
    protected function getSiteManager($qbCallback)
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

        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->expects($this->any())->method('getFieldNames')->will($this->returnValue(array(
            'name',
            'host',
        )));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $em->expects($this->any())->method('getRepository')->will($this->returnValue($repository));
        $em->expects($this->any())->method('getClassMetadata')->will($this->returnValue($metadata));

        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())->method('getManagerForClass')->will($this->returnValue($em));

        return new SiteManager('Sonata\PageBundle\Entity\BaseSite', $registry);
    }

    public function testGetPager()
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with(array());
                $qb->expects($self->once())->method('orderBy')->with(
                    $self->equalTo('s.name'),
                    $self->equalTo('ASC')
                );
            })
            ->getPager(array(), 1);
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Invalid sort field 'invalid' in 'Sonata\PageBundle\Entity\BaseSite' class
     */
    public function testGetPagerWithInvalidSort()
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self) { })
            ->getPager(array(), 1, 10, array('invalid' => 'ASC'));
    }

    public function testGetPagerWithMultipleSort()
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with(array());
                $qb->expects($self->exactly(2))->method('orderBy')->with(
                    $self->logicalOr(
                        $self->equalTo('s.name'),
                        $self->equalTo('s.host')
                    ),
                    $self->logicalOr(
                        $self->equalTo('ASC'),
                        $self->equalTo('DESC')
                    )
                );
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array()));
            })
            ->getPager(array(), 1, 10, array(
                'name' => 'ASC',
                'host'  => 'DESC',
            ));
    }

    public function testGetPagerWithEnabledSites()
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('enabled' => true)));
            })
            ->getPager(array('enabled' => true), 1);
    }

    public function testGetPagerWithDisabledSites()
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('enabled' => false)));
            })
            ->getPager(array('enabled' => false), 1);
    }

    public function testGetPagerWithDefaultSites()
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.isDefault = :isDefault'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('isDefault' => true)));
            })
            ->getPager(array('is_default' => true), 1);
    }

    public function testGetPagerWithNonDefaultSites()
    {
        $self = $this;
        $this
            ->getSiteManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.isDefault = :isDefault'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('isDefault' => false)));
            })
            ->getPager(array('is_default' => false), 1);
    }
}
