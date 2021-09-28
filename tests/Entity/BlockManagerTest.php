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

namespace Sonata\PageBundle\Tests\Entity;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BasePage;
use Sonata\PageBundle\Entity\BlockManager;

class BlockManagerTest extends TestCase
{
    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testGetPager(): void
    {
        $self = $this;
        $this
            ->getBlockManager(static function ($qb) use ($self) {
                $qb->expects($self->never())->method('join');
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo([]));
            })
            ->getPager(['root' => true], 1);
    }

    protected function getBlockManager($qbCallback): BlockManager
    {
        $query = $this->getMockForAbstractClass(AbstractQuery::class, [], '', false, true, true, ['execute']);
        $query->method('execute')->willReturn(true);

        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->setConstructorArgs([$this->createMock(EntityManager::class)])
            ->getMock();

        $qb->method('getRootAliases')->willReturn([]);
        $qb->method('select')->willReturn($qb);
        $qb->method('getQuery')->willReturn($query);

        $qbCallback($qb);

        $repository = $this->createMock(EntityRepository::class);
        $repository->method('createQueryBuilder')->willReturn($qb);

        $em = $this->createStub(EntityManager::class);
        $em->method('getRepository')->willReturn($repository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        return new BlockManager(BasePage::class, $registry);
    }
}
