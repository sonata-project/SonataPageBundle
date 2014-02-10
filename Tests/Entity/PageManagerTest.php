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
}
