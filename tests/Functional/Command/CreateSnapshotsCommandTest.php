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

namespace Sonata\PageBundle\Tests\Functional\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPagePage;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSite;
use Sonata\PageBundle\Tests\App\Entity\SonataPageSnapshot;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateSnapshotsCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        static::bootKernel();

        $this->commandTester = new CommandTester(
            (new Application(static::createKernel()))->find('sonata:page:create-snapshots')
        );
    }

    public function testCreateSnapshotsForAllSites(): void
    {
        $this->prepareData();

        static::assertSame(0, $this->countSnapshots());

        $this->commandTester->execute([]);

        static::assertSame(2, $this->countSnapshots());

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
    }

    public function testCreateSnapshotsForOneSite(): void
    {
        $this->prepareData();

        static::assertSame(0, $this->countSnapshots());

        $this->commandTester->execute(['--site' => [1]]);

        static::assertSame(1, $this->countSnapshots());

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
    }

    private function prepareData(): void
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        $site = new SonataPageSite();
        $site->setName('name');
        $site->setHost('localhost');

        $site2 = new SonataPageSite();
        $site2->setName('another_site');
        $site2->setHost('sonata-project.org');

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setTemplateCode('default');
        $page->setSite($site);

        $page2 = new SonataPagePage();
        $page2->setName('anoter_page');
        $page2->setTemplateCode('default');
        $page2->setSite($site2);

        $manager->persist($site);
        $manager->persist($site2);
        $manager->persist($page);
        $manager->persist($page2);

        $manager->flush();
    }

    private function countSnapshots(): int
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        return $manager->getRepository(SonataPageSnapshot::class)->count([]);
    }
}
