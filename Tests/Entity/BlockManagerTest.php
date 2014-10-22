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

use Sonata\PageBundle\Entity\BlockManager;

/**
 * Class BlockManagerTest
 *
 */
class BlockManagerTest extends \PHPUnit_Framework_TestCase
{
    protected function getBlockManager($qbCallback)
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

        return new BlockManager('Sonata\PageBundle\Entity\BasePage', $registry);
    }

    public function testGetPager()
    {
        $self = $this;
        $this
            ->getBlockManager(function ($qb) use ($self) {
                $qb->expects($self->never())->method('join');
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array()));
            })
            ->getPager(array('root' => true), 1);
    }
}
