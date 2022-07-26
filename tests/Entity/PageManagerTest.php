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

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\PageManager;
use Sonata\PageBundle\Tests\Model\Page;

final class PageManagerTest extends TestCase
{
    public function testFixUrl(): void
    {
        $manager = new PageManager(
            'Foo\Bar',
            $this->createMock(ManagerRegistry::class),
            []
        );

        $page1 = new Page();
        $page1->setName('Salut comment ca va ?');

        $page2 = new Page();
        $page2->setName('Super! et toi ?');

        $page1->addChild($page2);

        $manager->fixUrl($page1);

        static::assertSame('', $page1->getSlug());
        static::assertSame('/', $page1->getUrl());

        // if a parent page becomes a child page, then the slug and the url must be updated
        $parent = new Page();
        $parent->addChild($page1);

        $manager->fixUrl($parent);

        static::assertSame('', $parent->getSlug());
        static::assertSame('/', $parent->getUrl());

        static::assertSame('salut-comment-ca-va', $page1->getSlug());
        static::assertSame('/salut-comment-ca-va', $page1->getUrl());

        static::assertSame('super-et-toi', $page2->getSlug());
        static::assertSame('/salut-comment-ca-va/super-et-toi', $page2->getUrl());

        // check to remove the parent, so $page1 becomes a parent
        $page1->setParent(null);
        $manager->fixUrl($parent);

        static::assertSame('', $page1->getSlug());
        static::assertSame('/', $page1->getUrl());
    }

    public function testWithSlashAtTheEnd(): void
    {
        $manager = new PageManager(
            'Foo\Bar',
            $this->createMock(ManagerRegistry::class),
            []
        );

        $homepage = new Page();
        $homepage->setUrl('/');
        $homepage->setName('homepage');

        $bundle = new Page();
        $bundle->setUrl('/bundles/');
        $bundle->setName('Bundles');

        $child = new Page();
        $child->setName('foobar');

        $bundle->addChild($child);
        $homepage->addChild($bundle);

        $manager->fixUrl($child);

        static::assertSame('/bundles/foobar', $child->getUrl());
    }

    public function testCreateWithGlobalDefaults(): void
    {
        $manager = new PageManager(
            Page::class,
            $this->createMock(ManagerRegistry::class),
            [],
            ['my_route' => ['decorate' => false, 'name' => 'Salut!']]
        );

        $page = $manager->createWithDefaults(['name' => 'My Name', 'routeName' => 'my_route']);

        static::assertSame('My Name', $page->getName());
        static::assertFalse($page->getDecorate());
    }
}
