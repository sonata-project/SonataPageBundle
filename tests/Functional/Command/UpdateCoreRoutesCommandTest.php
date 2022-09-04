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
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class UpdateCoreRoutesCommandTest extends KernelTestCase
{
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        static::bootKernel();

        $this->commandTester = new CommandTester(
            (new Application(static::createKernel()))->find('sonata:page:update-core-routes')
        );
    }

    public function testUpdateCoreRoutesForAllSites(): void
    {
        $this->prepareData();

        static::assertSame(2, $this->countPages());

        $this->commandTester->execute([]);

        static::assertSame(16, $this->countPages());

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
    }

    public function testUpdateCoreRoutesForOneSites(): void
    {
        $this->prepareData();

        static::assertSame(2, $this->countPages());

        $this->commandTester->execute(['--site' => [1]]);

        static::assertSame(9, $this->countPages());

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
    }

    public function testUpdateCoreRoutesWithCleanup(): void
    {
        $this->prepareData();

        static::assertSame(2, $this->countPages());

        $this->commandTester->execute([
            '--site' => [1],
            '--clean' => true,
        ]);

        static::assertSame(8, $this->countPages());

        static::assertStringContainsString('done!', $this->commandTester->getDisplay());
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

        $site2 = new SonataPageSite();
        $site2->setName('another_site');
        $site2->setHost('sonata-project.org');

        $page = new SonataPagePage();
        $page->setName('name');
        $page->setRouteName('random_non_existent_route');
        $page->setTemplateCode('default');
        $page->setSite($site);

        $page2 = new SonataPagePage();
        $page2->setName('name');
        $page2->setRouteName('sonata_admin_dashboard');
        $page2->setTemplateCode('default');
        $page2->setSite($site);

        $manager->persist($site);
        $manager->persist($site2);
        $manager->persist($page);
        $manager->persist($page2);

        $manager->flush();
    }

    /**
     * @psalm-suppress UndefinedPropertyFetch
     */
    private function countPages(): int
    {
        // TODO: Simplify this when dropping support for Symfony 4.
        // @phpstan-ignore-next-line
        $container = method_exists(static::class, 'getContainer') ? static::getContainer() : static::$container;
        $manager = $container->get('doctrine.orm.entity_manager');
        \assert($manager instanceof EntityManagerInterface);

        return $manager->getRepository(SonataPagePage::class)->count([]);
    }
}
