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

use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\Command\CreateSnapshotsCommand;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class CreateSnapshotsCommandTest extends KernelTestCase
{
    private $siteManagerMock;
    private $application;

    protected function setUp(): void
    {
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

    /**
     * @test
     * @testdox It's creating a snapshot using "async" mode.
     * @TODO REMOVE NEXT_MAJOR
     */
    public function createOneSnapshotAsync(): void
    {
        //Mock
        $backendMock = $this->createMock(BackendInterface::class);
        $backendMock
            ->expects(static::once())
            ->method('createAndPublish');

        //Set mock services
        self::$container->set('sonata.page.manager.site', $this->siteManagerMock);
        self::$container->set('sonata.notification.backend', $backendMock);

        //Command
        $command = $this->application->find('sonata:page:create-snapshots');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => [1],
            '--mode' => 'async',
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('done!', $output);
    }

    /**
     * @test
     * @testdox it's using notificationBundle when mode option is equals "async"
     * @testWith ["sync", 0]
     *           ["async", 1]
     */
    public function callNotificationBackend(string $mode, int $notificationWillBeExecuted): void
    {
        // Mocks
        $commandMock = $this->createPartialMock(CreateSnapshotsCommand::class, [
            'getNotificationBackend',
            'getSites',
        ]);
        $commandMock
            ->expects(static::once())
            ->method('getSites')
            ->willReturn([$this->createMock(Site::class)]);
        $commandMock
            ->expects(static::exactly($notificationWillBeExecuted))
            ->method('getNotificationBackend')
            ->willReturn($this->createMock(BackendInterface::class));

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock
            ->method('getOption')
            ->willReturn($mode);

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->expects(static::exactly(2))
            ->method('writeln');

        // Run code
        $output = $commandMock->execute($inputMock, $outputMock);

        // Assert
        static::assertSame(0, $output);
    }
}
