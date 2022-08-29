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

namespace Sonata\PageBundle\Tests\Functional\Ticket;

use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Issue1134Test extends WebTestCase
{
    public function testLabelInShowAction(): void
    {
        $client = static::createClient();

        $site = new SonataPageSite();
        $site->setId(1);
        $site->setName('name');
        $site->setHost('http://localhost');
        $site->setIsDefault(true);

        $entityManager = $client->getContainer()->get('doctrine')->getManager();
        $entityManager->persist($site);
        $entityManager->flush();

        $tokenManager = $client->getContainer()->get('security.csrf.token_manager');
        $csrfToken = $tokenManager->getToken('sonata.batch');

        $crawler = $client->request(
            Request::METHOD_POST,
            '/admin/tests/app/sonatapagepage/batch?filter%5Bsite%5D%5Bvalue%5D=1',
            [
                'all_elements' => '1',
                'action' => 'snapshot',
                '_sonata_csrf_token' => $csrfToken->getValue(),
            ]
        );

        $form = $crawler->selectButton('Yes, execute')->form();

        $client->submit($form);

        $client->request('GET', $client->getResponse()->headers->get('Location'));

        static::assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}
