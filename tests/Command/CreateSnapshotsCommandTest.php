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

namespace Sonata\PageBundle\Tests\Command;

use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CreateSnapshotsCommandTest extends KernelTestCase
{
    private $siteManagerMock;
    private $application;

    protected function setUp(): void
    {
        parent::setUp();
        //Mocks
        $siteMock = $this->createMock(SiteInterface::class);
        $siteMock
            ->method('getId')
            ->willReturn(1);
        $siteMock
            ->method('getName')
            ->willReturn('foo');
        $siteMock
            ->method('getUrl')
            ->willReturn('https://bar.baz');

        $siteManagerMock = $this->createMock(SiteManagerInterface::class);
        $siteManagerMock
            ->method('findBy')
            ->willReturn([$siteMock]);

        // Setup SymfonyKernel
        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
        $this->siteManagerMock = $siteManagerMock;
    }

    public function testCreateSnapshot(): void
    {
        //Set mock services
        self::$container->set('sonata.page.manager.site', $this->siteManagerMock);

        //Command
        $command = $this->application->find('sonata:page:create-snapshots');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => [1],
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('done!', $output);
    }

    /**
     * NEXT_MAJOR: remove the dataProvider, because the notification Bundle will be removed and the legacy group.
     *
     * @group legacy
     */
    public function testCallNotificationBackend(): void
    {
        //Set mock services
        self::$container->set('sonata.page.manager.site', $this->siteManagerMock);

        //Command
        $command = $this->application->find('sonata:page:create-snapshots');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => [1],
            '--mode' => 'sync',
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('done!', $output);
    }

    /**
     * We are requiring this argument to work like "doctrine:schema:update --force"
     * You can check more details here: https://github.com/sonata-project/SonataPageBundle/pull/1418#discussion_r912350492.
     */
    public function testRequireSiteAllArgument()
    {
        $command = $this->application->find('sonata:page:create-snapshots');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('Please provide an --site=SITE_ID option or the --site=all directive', $output);
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
