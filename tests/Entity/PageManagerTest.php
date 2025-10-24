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
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\PageManager;
use Sonata\PageBundle\Service\Contract\FixPageUrlInterface;
use Sonata\PageBundle\Service\FixPageUrlService;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class PageManagerTest extends TestCase
{
    /**
     * NEXT_MAJOR: Remove this data provider and keep only "FixPageUrlService" in tests that is using it.
     *
     * @return iterable<array<SlugifyInterface|FixPageUrlInterface>>
     **/
    public static function provideFixUrlCases(): iterable
    {
        yield [new Slugify()];
        yield [new FixPageUrlService(new AsciiSlugger())];
    }

    #[DataProvider('provideFixUrlCases')]
    public function testFixUrl(SlugifyInterface|FixPageUrlInterface $fixPageUrl): void
    {
        $manager = new PageManager(
            Page::class,
            static::createStub(ManagerRegistry::class),
            $fixPageUrl,
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

    #[DataProvider('provideFixUrlCases')]
    public function testWithSlashAtTheEnd(SlugifyInterface|FixPageUrlInterface $fixPageUrl): void
    {
        $manager = new PageManager(
            Page::class,
            $this->createMock(ManagerRegistry::class),
            $fixPageUrl,
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

    #[DataProvider('provideFixUrlCases')]
    public function testCreateWithGlobalDefaults(SlugifyInterface|FixPageUrlInterface $fixPageUrl): void
    {
        $manager = new PageManager(
            Page::class,
            $this->createMock(ManagerRegistry::class),
            $fixPageUrl,
            [],
            ['my_route' => ['decorate' => false, 'name' => 'Salut!']]
        );

        $page = $manager->createWithDefaults(['name' => 'My Name', 'routeName' => 'my_route']);

        static::assertSame('My Name', $page->getName());
        static::assertFalse($page->getDecorate());
    }
}
