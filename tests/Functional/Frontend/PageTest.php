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
        $block->setParent($containerBlock);

        $block2 = new SonataPageBlock();
        $block2->setType('sonata.page.block.pagelist');
        $block2->setParent($containerBlock);

        $block3 = new SonataPageBlock();
        $block3->setType('sonata.page.block.children_pages');
        $block3->setSetting('pageId', 1);
        $block3->setParent($containerBlock);

        $block4 = new SonataPageBlock();
        $block4->setType('sonata.page.block.breadcrumb');
        $block4->setParent($containerBlock);

        $page->addBlock($containerBlock);
        $page->addBlock($block);
        $page->addBlock($block2);
        $page->addBlock($block3);
        $page->addBlock($block4);

        $manager->persist($site);
        $manager->persist($page);
        $manager->persist($containerBlock);
        $manager->persist($block);
        $manager->persist($block2);
        $manager->persist($block3);
        $manager->persist($block4);

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
