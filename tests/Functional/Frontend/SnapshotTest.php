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

        $page->addBlock($containerBlock);

        $manager->persist($site);
        $manager->persist($page);
        $manager->persist($containerBlock);

        $manager->flush();
    }
}
