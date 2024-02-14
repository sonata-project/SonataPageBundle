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

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\PageManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;

final class PageManagerTest extends TestCase
{
    /**
     * @group legacy
     *
     * NEXT_MAJOR: Remove slugfy test
     **/
    public function testFixUrlWithSlugfy(): void
    {
        $manager = new PageManager(
            Page::class,
            $this->createStub(ManagerRegistry::class),
            new Slugify()
        );

        $page1 = new Page();
        $page1->setName('Salut comment ca va ?');

        $page2 = new Page();
        $page2->setName('Super! et toi ?');

        $page1->addChild($page2);

        $parent = new Page();
        $parent->addChild($page1);

        $manager->fixUrl($page1);
        static::assertSame('salut-comment-ca-va', $page1->getSlug());
    }

    public function testFixUrl(): void
    {
        $manager = new PageManager(
            Page::class,
            $this->createStub(ManagerRegistry::class),
            new AsciiSlugger()
        );

        $page1 = new Page();
        $page1->setName('Salut comment ca va ?');

        $page2 = new Page();
        $page2->setName('Super! et toi ?');

        $page1->addChild($page2);

        $manager->fixUrl($page1);

        static::assertNull($page1->getSlug());
        static::assertSame('/', $page1->getUrl());

        // if a parent page becomes a child page, then the slug and the url must be updated
        $parent = new Page();
        $parent->addChild($page1);

        $manager->fixUrl($parent);

        static::assertNull($parent->getSlug());
        static::assertSame('/', $parent->getUrl());

        static::assertSame('salut-comment-ca-va', $page1->getSlug());
        static::assertSame('/salut-comment-ca-va', $page1->getUrl());

        static::assertSame('super-et-toi', $page2->getSlug());
        static::assertSame('/salut-comment-ca-va/super-et-toi', $page2->getUrl());

        // check to remove the parent, so $page1 becomes a parent
        $page1->setParent(null);
        $manager->fixUrl($parent);

        static::assertNull($page1->getSlug());
        static::assertSame('/', $page1->getUrl());
    }

    public function testWithSlashAtTheEnd(): void
    {
        $manager = new PageManager(
            Page::class,
            $this->createMock(ManagerRegistry::class),
            new AsciiSlugger()
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
            new Slugify(),
            [],
            ['my_route' => ['decorate' => false, 'name' => 'Salut!']]
        );

        $page = $manager->createWithDefaults(['name' => 'My Name', 'routeName' => 'my_route']);

        static::assertSame('My Name', $page->getName());
        static::assertFalse($page->getDecorate());
    }

    public function testInternalPageHasNoUrl(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $page = $this->createMock(PageInterface::class);

        $page
            ->expects(static::once())
            ->method('isInternal')
            ->willReturn(true);
        $page
            ->expects(static::any())
            ->method('getChildren')
            ->willReturn(new ArrayCollection());
        $page
            ->expects(static::once())
            ->method('setUrl')
            ->with(static::isNull());

        $manager = new PageManager(
            Page::class,
            $registry,
            $slugger,
            [],
            []
        );

        $manager->fixUrl($page);
    }

    public function testHybridPageIsNotProcessed(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $page = $this->createMock(PageInterface::class);

        $page
            ->expects(static::once())
            ->method('isInternal')
            ->willReturn(false);
        $page
            ->expects(static::once())
            ->method('isHybrid')
            ->willReturn(true);
        $page
            ->expects(static::once())
            ->method('getChildren')
            ->willReturn(new ArrayCollection());
        $page
            ->expects(static::never())
            ->method('setUrl');

        $manager = new PageManager(
            Page::class,
            $registry,
            $slugger,
            [],
            []
        );

        $manager->fixUrl($page);
    }

    public function testSetCustomUrlWhenParentIsNull(): void
    {
        $slugger = $this->createMock(SluggerInterface::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $page = $this->createMock(PageInterface::class);

        $page
            ->expects(static::once())
            ->method('isInternal')
            ->willReturn(false);
        $page
            ->expects(static::once())
            ->method('isHybrid')
            ->willReturn(false);
        $page
            ->expects(static::once())
            ->method('getChildren')
            ->willReturn(new ArrayCollection());
        $page
            ->expects(static::once())
            ->method('getCustomUrl')
            ->willReturn('foo-custom-url');
        $page
            ->expects(static::once())
            ->method('setUrl')
            ->with('/foo-custom-url');

        $manager = new PageManager(
            Page::class,
            $registry,
            $slugger,
            [],
            []
        );

        $manager->fixUrl($page);
    }
}
