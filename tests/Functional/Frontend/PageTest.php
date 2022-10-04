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

namespace Sonata\PageBundle\Tests\Functional\Frontend;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class PageTest extends WebTestCase
{
    public function testCreatePageAndRender(): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/admin/tests/app/sonatapagepage/create', [
            'uniqid' => 'page',
            'siteId' => 1,
        ]);

        $client->submitForm('btn_create_and_list', [
            'page[name]' => 'Name',
            'page[enabled]' => 1,
            'page[templateCode]' => 'default',
            'page[customUrl]' => '/custom-url',
        ]);
        $client->followRedirect();

        $client->request('GET', '/custom-url');

        self::assertResponseStatusCodeSame(404);

        $this->becomeEditor($client);

        $client->request('GET', '/custom-url');

        self::assertResponseIsSuccessful();
    }

    public function testPageRenderFromPageWhenUserIsAnEditor(): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/');

        self::assertResponseStatusCodeSame(404);

        $this->becomeEditor($client);

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
    }

    /**
     * @dataProvider providePages
     *
     * @param array<string> $shouldContain
     * @param array<string> $shouldNotContain
     */
    public function testPageRender(PageInterface $page, string $url, int $statusCode, array $shouldContain, array $shouldNotContain): void
    {
        $client = self::createClient();

        $this->preparePageTypesData($page);
        $this->becomeEditor($client);

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
    public static function providePages(): iterable
    {
        yield 'CMS Page' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/hybrid');

            return $page;
        })(), '/hybrid', 200, ['Page content'], ['Original content']];

        yield 'Disabled Page' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setUrl('/hybrid');

            return $page;
        })(), '/hybrid', 200, ['Page content'], ['Original content']];

        yield 'Hybrid Page without decoration' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/hybrid');
            $page->setRouteName('hybrid_route');
            $page->setDecorate(false);

            return $page;
        })(), '/hybrid', 200, ['Original content'], ['Page content']];

        yield 'Hybrid Page' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/hybrid');
            $page->setRouteName('hybrid_route');

            return $page;
        })(), '/hybrid', 200, ['Original content', 'Page content'], []];

        yield 'Non existent hybrid Page' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setRouteName('random_route');

            return $page;
        })(), '/random_route', 404, ['The page does not exist'], ['Page content']];

        yield 'Dynamic Page without decoration' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/dynamic/{id}');
            $page->setRouteName('dynamic_route');
            $page->setDecorate(false);

            return $page;
        })(), '/dynamic/20', 200, ['Original content 20'], ['Page content']];

        yield 'Dynamic Page' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/dynamic/{id}');
            $page->setRouteName('dynamic_route');

            return $page;
        })(), '/dynamic/25', 200, ['Original content 25', 'Page content'], []];

        yield 'Page with footer' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setRouteName('_page_internal_global');

            $containerBlock = new SonataPageBlock();
            $containerBlock->setType('sonata.page.block.container');
            $containerBlock->setSetting('code', 'footer');
            $containerBlock->setEnabled(true);

            $block = new SonataPageBlock();
            $block->setType('sonata.block.service.text');
            $block->setSetting('content', 'Footer content');

            $page->addBlock($containerBlock);
            $containerBlock->addChild($block);

            return $page;
        })(), '/random_route', 404, ['Footer content'], []];
    }

    public function testGlobalPage(): void
    {
        $client = self::createClient();

        $this->prepareGlobalPageData();
        $this->becomeEditor($client);

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

        $containerBlock = new SonataPageBlock();
        $containerBlock->setType('sonata.page.block.container');
        $containerBlock->setSetting('code', 'content');
        $containerBlock->setEnabled(true);

        $block = new SonataPageBlock();
        $block->setType('sonata.page.block.shared_block');
        $block->setSetting('blockId', 3);

        $block2 = new SonataPageBlock();
        $block2->setType('sonata.page.block.pagelist');

        $block3 = new SonataPageBlock();
        $block3->setType('sonata.page.block.children_pages');
        $block3->setSetting('pageId', 1);

        $block4 = new SonataPageBlock();
        $block4->setType('sonata.page.block.breadcrumb');

        $containerBlock->addChild($block);
        $containerBlock->addChild($block2);
        $containerBlock->addChild($block3);
        $containerBlock->addChild($block4);

        $page->addBlock($containerBlock);
        $page->addBlock($block);
        $page->addBlock($block2);
        $page->addBlock($block3);
        $page->addBlock($block4);

        $manager->persist($site);
        $manager->persist($page);

        $manager->flush();
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function preparePageTypesData(PageInterface $page): void
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

        $containerBlock->addChild($block);

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
    private function prepareGlobalPageData(): void
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

        $block2 = new SonataPageBlock();
        $block2->setType('sonata.block.service.text');
        $block2->setSetting('content', 'Page content');

        $page->addBlock($containerBlock);
        $page2->addBlock($containerBlock2);
        $containerBlock->addChild($block);
        $containerBlock2->addChild($block2);

        $manager->persist($site);
        $manager->persist($page);
        $manager->persist($page2);

        $manager->flush();
    }

    /**
     * Normally this would happen via an interactive login.
     * Part of this logic is also copied from AbstractBrowser::loginUser().
     *
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function becomeEditor(AbstractBrowser $client): void
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists($this, 'getContainer') ? self::getContainer() : self::$container;

        // TODO: Simplify this when dropping support for Symfony 4.
        if ($container->has('session.factory')) {
            $sessionFactory = $container->get('session.factory');
            \assert($sessionFactory instanceof SessionFactoryInterface);

            $session = $sessionFactory->createSession();
        } else {
            $session = $container->get('session');
            \assert($session instanceof SessionInterface);
        }

        $session->set('sonata/page/isEditor', true);
        $session->save();

        $domains = array_unique(array_map(
            static fn (Cookie $cookie) => $cookie->getName() === $session->getName() ? $cookie->getDomain() : '',
            $client->getCookieJar()->all()
        ));
        $domains = [] !== $domains ? $domains : [''];

        foreach ($domains as $domain) {
            $cookie = new Cookie($session->getName(), $session->getId(), null, null, $domain);
            $client->getCookieJar()->set($cookie);
        }
    }
}
