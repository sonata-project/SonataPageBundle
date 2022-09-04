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

namespace Sonata\PageBundle\Tests\Frontend;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SnapshotTest extends WebTestCase
{
    public function testPageRenderFromSnapshot(): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/');

        self::assertResponseStatusCodeSame(404);

        $client->request('GET', '/admin/tests/app/sonatapagesite/1/snapshots');
        $client->submitForm('create');

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
    }

    /**
     * @dataProvider provideSnapshots
     *
     * @param array<string> $shouldContain
     * @param array<string> $shouldNotContain
     */
    public function testSnapshotRender(PageInterface $page, string $url, int $statusCode, array $shouldContain, array $shouldNotContain): void
    {
        $client = self::createClient();

        $this->prepareSnapshotTypesData($page);

        $client->request('GET', '/admin/tests/app/sonatapagesite/1/snapshots');
        $client->submitForm('create');

        $client->request('GET', $url);

        self::assertResponseStatusCodeSame($statusCode);

        $content = $client->getResponse()->getContent();
        \assert(false !== $content);

        foreach ($shouldContain as $string) {
            static::assertStringContainsString($string, $content);
        }

        foreach ($shouldNotContain as $string) {
            static::assertStringNotContainsString($string, $content);
        }
    }

    /**
     * @return iterable<array<PageInterface|array<string>|string|int>>
     *
     * @phpstan-return iterable<array{0: PageInterface, 1: string, 2: int, 3: array<string>, 4: array<string>}>
     */
    public static function provideSnapshots(): iterable
    {
        yield 'CMS Snapshot' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/hybrid');

            return $page;
        })(), '/hybrid', 200, ['Page content'], ['Original content']];

        yield 'Disabled Snapshot' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setUrl('/hybrid');

            return $page;
        })(), '/hybrid', 500, [], ['Original content', 'Page content']];

        yield 'Hybrid Snapshot without decoration' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/hybrid');
            $page->setRouteName('hybrid_route');
            $page->setDecorate(false);

            return $page;
        })(), '/hybrid', 200, ['Original content'], ['Page content']];

        yield 'Hybrid Snapshot' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/hybrid');
            $page->setRouteName('hybrid_route');

            return $page;
        })(), '/hybrid', 200, ['Original content', 'Page content'], []];

        yield 'Non existent hybrid Snapshot' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setRouteName('random_route');

            return $page;
        })(), '/random_route', 404, [], ['Page content']];

        yield 'Dynamic Snapshot without decoration' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/dynamic/{id}');
            $page->setRouteName('dynamic_route');
            $page->setDecorate(false);

            return $page;
        })(), '/dynamic/20', 200, ['Original content 20'], ['Page content']];

        yield 'Dynamic Snapshot' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/dynamic/{id}');
            $page->setRouteName('dynamic_route');

            return $page;
        })(), '/dynamic/25', 200, ['Original content 25', 'Page content'], []];
    }

    public function testGlobalSnapshot(): void
    {
        $client = self::createClient();

        $this->prepareGlobalSnapshotData();

        $client->request('GET', '/admin/tests/app/sonatapagesite/1/snapshots');
        $client->submitForm('create');

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        \assert(false !== $content);

        static::assertStringContainsString('Page content', $content);
        static::assertStringContainsString('Footer content', $content);
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function prepareData(): void
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists($this, 'getContainer') ? self::getContainer() : self::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $site = new SonataPageSite();
        $site->setName('name');
        $site->setHost('localhost');
        $site->setEnabled(true);

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setUrl('/');
        $page->setTemplateCode('default');
        $page->setEnabled(true);
        $page->setSite($site);

        $manager->persist($site);
        $manager->persist($page);

        $manager->flush();
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function prepareSnapshotTypesData(PageInterface $page): void
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists($this, 'getContainer') ? self::getContainer() : self::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $site = new SonataPageSite();
        $site->setName('name');
        $site->setHost('localhost');
        $site->setEnabled(true);

        $containerBlock = new SonataPageBlock();
        $containerBlock->setType('sonata.page.block.container');
        $containerBlock->setSetting('code', 'content_top');
        $containerBlock->setEnabled(true);

        $block = new SonataPageBlock();
        $block->setType('sonata.block.service.text');
        $block->setSetting('content', 'Page content');
        $block->setParent($containerBlock);

        $page->setSite($site);
        $page->addBlock($containerBlock);
        $page->addBlock($block);

        $manager->persist($site);
        $manager->persist($page);

        $manager->flush();
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function prepareGlobalSnapshotData(): void
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists($this, 'getContainer') ? self::getContainer() : self::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $site = new SonataPageSite();
        $site->setName('name');
        $site->setHost('localhost');
        $site->setEnabled(true);

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setUrl('/');
        $page->setTemplateCode('default');
        $page->setEnabled(true);
        $page->setSite($site);

        $page2 = new SonataPagePage();
        $page2->setName('global');
        $page2->setTemplateCode('default');
        $page2->setEnabled(true);
        $page2->setRouteName('_page_internal_global');
        $page2->setSite($site);

        $containerBlock = new SonataPageBlock();
        $containerBlock->setType('sonata.page.block.container');
        $containerBlock->setSetting('code', 'content_top');
        $containerBlock->setEnabled(true);

        $containerBlock2 = new SonataPageBlock();
        $containerBlock2->setType('sonata.page.block.container');
        $containerBlock2->setSetting('code', 'footer');
        $containerBlock2->setEnabled(true);

        $block = new SonataPageBlock();
        $block->setType('sonata.block.service.text');
        $block->setSetting('content', 'Footer content');
        $block->setParent($containerBlock);

        $block2 = new SonataPageBlock();
        $block2->setType('sonata.block.service.text');
        $block2->setSetting('content', 'Page content');
        $block2->setParent($containerBlock2);

        $page->addBlock($containerBlock);
        $page->addBlock($block);
        $page2->addBlock($containerBlock2);
        $page2->addBlock($block2);

        $manager->persist($site);
        $manager->persist($page);
        $manager->persist($page2);

        $manager->flush();
    }
}
