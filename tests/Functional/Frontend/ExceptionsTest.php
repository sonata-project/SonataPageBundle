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
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;

final class ExceptionsTest extends WebTestCase
{
    public function testExceptionListWithNoAccess(): void
    {
        $client = self::createClient();

        $this->prepareData();

        $client->request('GET', '/exceptions/list');

        self::assertResponseStatusCodeSame(404);
    }

    public function testExceptionsListWithEditorAccess(): void
    {
        $client = self::createClient();

        $this->becomeEditor($client);
        $this->prepareData();

        $client->request('GET', '/exceptions/list');

        self::assertResponseIsSuccessful();
    }

    public function testExceptionsEdit(): void
    {
        $client = self::createClient();

        $this->becomeEditor($client);
        $this->prepareData();

        $client->request('GET', '/exceptions/edit/404');

        self::assertResponseIsSuccessful();
    }

    private function prepareData(): void
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $site = new SonataPageSite();
        $site->setName('name');
        $site->setHost('localhost');
        $site->setEnabled(true);

        $page = new SonataPagePage();
        $page->setName('exceptions_list');
        $page->setRouteName('sonata_page_exceptions_list');
        $page->setTemplateCode('default');
        $page->setSite($site);

        $page2 = new SonataPagePage();
        $page2->setName('error_not_found');
        $page2->setRouteName('_page_internal_error_not_found');
        $page2->setTemplateCode('default');
        $page2->setSite($site);

        $manager->persist($site);
        $manager->persist($page);
        $manager->persist($page2);

        $manager->flush();
    }

    /**
     * Normally this would happen via an interactive login.
     * Part of this logic is also copied from AbstractBrowser::loginUser().
     */
    private function becomeEditor(AbstractBrowser $client): void
    {
        $sessionFactory = self::getContainer()->get('session.factory');
        \assert($sessionFactory instanceof SessionFactoryInterface);

        $session = $sessionFactory->createSession();

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
