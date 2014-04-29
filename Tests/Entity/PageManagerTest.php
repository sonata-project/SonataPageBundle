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

use Sonata\PageBundle\Tests\Model\Page;
use Sonata\PageBundle\Entity\PageManager;

/**
 * Class PageManagerTest
 *
 */
class PageManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testFixUrl()
    {
        $entityManager = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry', array(), array(), '', false);

        $manager = new PageManager('Foo\Bar', $entityManager, array());

        $page1 = new Page;
        $page1->setName('Salut comment ca va ?');

        $page2 = new Page;
        $page2->setName('Super! et toi ?');

        $page1->addChildren($page2);

        $manager->fixUrl($page1);

        $this->assertEquals(null, $page1->getSlug());
        $this->assertEquals('/', $page1->getUrl());

        // if a parent page becaume a child page, then the slug and the url must be updated
        $parent = new Page;
        $parent->addChildren($page1);

        $manager->fixUrl($parent);

        $this->assertEquals(null, $parent->getSlug());
        $this->assertEquals('/', $parent->getUrl());

        $this->assertEquals('salut-comment-ca-va', $page1->getSlug());
        $this->assertEquals('/salut-comment-ca-va', $page1->getUrl());

        $this->assertEquals('super-et-toi', $page2->getSlug());
        $this->assertEquals('/salut-comment-ca-va/super-et-toi', $page2->getUrl());

        // check to remove the parent, so $page1 becaume a parent
        $page1->setParent(null);
        $manager->fixUrl($parent);

        $this->assertEquals(null, $page1->getSlug());
        $this->assertEquals('/', $page1->getUrl());
    }

    public function testWithSlashAtTheEnd()
    {
        $entityManager = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry', array(), array(), '', false);

        $manager = new PageManager('Foo\Bar', $entityManager, array());

        $homepage = new Page();
        $homepage->setUrl('/');
        $homepage->setName('homepage');

        $bundle = new Page;
        $bundle->setUrl('/bundles/');
        $bundle->setName('Bundles');

        $child = new Page;
        $child->setName('foobar');

        $bundle->addChildren($child);
        $homepage->addChildren($bundle);

        $manager->fixUrl($child);

        $this->assertEquals('/bundles/foobar', $child->getUrl());
    }

    public function testCreateWithGlobalDefaults()
    {
        $entityManager = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry', array(), array(), '', false);

        $manager = new PageManager('Sonata\PageBundle\Tests\Model\Page', $entityManager, array(), array('my_route' => array('decorate' => false, 'name' => 'Salut!')));

        $page = $manager->create(array('name' => 'My Name', 'routeName' => 'my_route'));

        $this->assertEquals('My Name', $page->getName());
        $this->assertFalse($page->getDecorate());
    }

    protected function getPageManager($qbCallback)
    {
        $query = $this->getMockForAbstractClass('Doctrine\ORM\AbstractQuery', array(), '', false, true, true, array('execute'));
        $query->expects($this->any())->method('execute')->will($this->returnValue(true));

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();
        $qb->expects($this->any())->method('select')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('getQuery')->will($this->returnValue($query));

        $qbCallback($qb);

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repository->expects($this->any())->method('createQueryBuilder')->will($this->returnValue($qb));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $em->expects($this->any())->method('getRepository')->will($this->returnValue($repository));

        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())->method('getManagerForClass')->will($this->returnValue($em));

        return new PageManager('Sonata\PageBundle\Entity\BasePage', $registry);
    }

    public function testGetPagerWithRootPages()
    {
        $self = $this;
        $this
            ->getPageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.parent IS NULL'));
            })
            ->getPager(array('root' => true), 1);
    }

    public function testGetPagerWithNonRootPages()
    {
        $self = $this;
        $this
            ->getPageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.parent IS NOT NULL'));
            })
            ->getPager(array('root' => false), 1);
    }

    public function testGetPagerWithEnabledPages()
    {
        $self = $this;
        $this
            ->getPageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('enabled' => true)));
            })
            ->getPager(array('enabled' => true), 1);
    }

    public function testGetPagerWithDisabledPages()
    {
        $self = $this;
        $this
            ->getPageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.enabled = :enabled'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('enabled' => false)));
            })
            ->getPager(array('enabled' => false), 1);
    }

    public function testGetPagerWithEditedPages()
    {
        $self = $this;
        $this
            ->getPageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.edited = :edited'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('edited' => true)));
            })
            ->getPager(array('edited' => true), 1);
    }

    public function testGetPagerWithNonEditedPages()
    {
        $self = $this;
        $this
            ->getPageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('p.edited = :edited'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('edited' => false)));
            })
            ->getPager(array('edited' => false), 1);
    }

    public function testGetPagerWithParentChildPages()
    {
        $self = $this;
        $this
            ->getPageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('p.parent'),
                    $self->equalTo('pa')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('pa.id = :parentId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('parentId' => 13)));
            })
            ->getPager(array('parent' => 13), 1);
    }

    public function testGetPagerWithSitePages()
    {
        $self = $this;
        $this
            ->getPageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('join')->with(
                    $self->equalTo('p.site'),
                    $self->equalTo('s')
                );
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('s.id = :siteId'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('siteId' => 13)));
            })
            ->getPager(array('site' => 13), 1);
    }
}
