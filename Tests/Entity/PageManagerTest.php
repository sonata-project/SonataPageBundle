<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Cache;

use Sonata\PageBundle\Tests\Entity\Page;
use Sonata\PageBundle\Entity\PageManager;

class PageManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testFixUr()
    {
        $manager = new PageManager($this->getMock('Doctrine\ORM\EntityManager', array(), array(), '', false));

        $page1 = new Page;
        $page1->setName('Salut comment ca va ?');

        $page2 = new Page;
        $page2->setName('Super! et toi ?');

        $page1->addChildren($page2);

        $manager->fixUrl($page1);

        $this->assertEquals($page1->getSlug(), 'salut-comment-ca-va');
        $this->assertEquals($page1->getUrl(), '/salut-comment-ca-va');

        $parent = new Page;
        $parent->setRouteName('homepage');

        $parent->addChildren($page1);

        $manager->fixUrl($parent);

        $this->assertEquals($parent->getSlug(), null); // homepage is a specific route name
        $this->assertEquals($parent->getUrl(), '/');

        $this->assertEquals($page1->getSlug(), 'salut-comment-ca-va');
        $this->assertEquals($page1->getUrl(), '/salut-comment-ca-va');

        $this->assertEquals($page2->getSlug(), 'super-et-toi');
        $this->assertEquals($page2->getUrl(), '/salut-comment-ca-va/super-et-toi');
    }

    public function testSlugigy()
    {
        $this->assertEquals(Page::slugify('test'), 'test');
        $this->assertEquals(Page::slugify('S§!@@#$#$alut'), 's-alut');
        $this->assertEquals(Page::slugify('Symfony2'), 'symfony2');
        $this->assertEquals(Page::slugify('test'), 'test');
    }
}
