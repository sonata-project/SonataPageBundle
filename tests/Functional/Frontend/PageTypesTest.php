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
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class PageTypesTest extends WebTestCase
{
    /**
     * @dataProvider providePages
     *
     * @param array<string> $shouldContain
     * @param array<string> $shouldNotContain
     */
    public function testPageRender(PageInterface $page, array $shouldContain, array $shouldNotContain): void
    {
        $client = self::createClient();

        $this->prepareData($page);
        $this->becomeEditor($client);

        $url = $page->getUrl();
        \assert(null !== $url);

        $client->request('GET', $url);

        self::assertResponseIsSuccessful();

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
     * @return iterable<array<PageInterface|array<string>>>
     *
     * @phpstan-return iterable<array{0: PageInterface, 1: array<string>, 2: array<string>}>
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
        })(), ['Page content'], ['Original content']];

        yield 'Hybrid Page without decoration' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/hybrid');
            $page->setRouteName('hybrid_route');
            $page->setDecorate(false);

            return $page;
        })(), ['Original content'], ['Page content']];

        yield 'Hybrid Page' => [(static function () {
            $page = new SonataPagePage();
            $page->setName('name');
            $page->setTemplateCode('default');
            $page->setEnabled(true);
            $page->setUrl('/hybrid');
            $page->setRouteName('hybrid_route');

            return $page;
        })(), ['Original content', 'Page content'], []];
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function prepareData(PageInterface $page): void
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
        $manager->persist($containerBlock);
        $manager->persist($block);

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
