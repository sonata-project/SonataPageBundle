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
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotBySiteInterface;
use Sonata\PageBundle\Service\Contract\GetSitesFromCommand;
use Sonata\PageBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    /**
     * @group legacy
     *
     * NEXT_MAJOR: Remove this test.
     */
    public function testCreateOneSnapshotAsync(): void
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

    public function testCreateSnapshot()
    {
        //Mocks
        $createSnapshotsMock = $this->createMock(CreateSnapshotBySiteInterface::class);
        $createSnapshotsMock
            ->expects(static::once())
            ->method('createBySite')
            ->with(static::isInstanceOf(SiteInterface::class));

        $getSitesServiceMock = $this->createMock(GetSitesFromCommand::class);
        $getSitesServiceMock
            ->expects(static::once())
            ->method('findSitesById')
            ->willReturn([$this->createMock(SiteInterface::class)]);

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock
            ->method('get')
            ->with('sonata.page.service.get_sites')
            ->willReturn($getSitesServiceMock);

        $createSnapshotCommandMock = $this->getMockBuilder(CreateSnapshotsCommand::class)
            ->onlyMethods(['getContainer'])
            ->setConstructorArgs([$createSnapshotsMock])
            ->getMock();
        $createSnapshotCommandMock
            ->method('getContainer')
            ->willReturn($containerMock);

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock
            ->method('getOption')
            ->willReturnOnConsecutiveCalls(['all'], 'sync')
        ;

        $outputMock = $this->createMock(OutputInterface::class);

        //Run code
        $createSnapshotCommandMock->execute($inputMock, $outputMock);
    }

    /**
     * NEXT_MAJOR: remove the dataProvider, because the notification Bundle will be removed and the legacy group.
     *
     * @group legacy
     *
     * @dataProvider getProvidedDataCallNotificationBackend
     */
    public function testCallNotificationBackend(
        string $mode,
        int $notificationWillBeExecuted,
        int $createSnapshotServiceWillBeExecuted
    ): void {
        // Mocks
        $createSnapshotServiceMock = $this->createMock(CreateSnapshotBySiteInterface::class);
        $createSnapshotServiceMock
            ->expects(static::exactly($createSnapshotServiceWillBeExecuted))
            ->method('createBySite');

        $getSitesServiceMock = $this->createMock(GetSitesFromCommand::class);
        $getSitesServiceMock
            ->expects(static::once())
            ->method('findSitesById')
            ->willReturn([$this->createMock(SiteInterface::class)]);

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock
            ->method('get')
            ->with('sonata.page.service.get_sites')
            ->willReturn($getSitesServiceMock);

        $commandMock = $this
            ->getMockBuilder(CreateSnapshotsCommand::class)
            ->onlyMethods(['getNotificationBackend', 'getContainer'])
            ->setConstructorArgs([$createSnapshotServiceMock])
            ->getMock();

        $commandMock
            ->method('getContainer')
            ->willReturn($containerMock);

        $commandMock
            ->expects(static::exactly($notificationWillBeExecuted))
            ->method('getNotificationBackend')
            ->willReturn($this->createMock(BackendInterface::class));

        $inputMock = $this->createMock(InputInterface::class);
        $inputMock = $this->createMock(InputInterface::class);
        $inputMock
            ->method('getOption')
            ->willReturnOnConsecutiveCalls(['all'], $mode)
        ;

        $outputMock = $this->createMock(OutputInterface::class);
        $outputMock
            ->expects(static::exactly(2))
            ->method('writeln');

        // Run code
        $output = $commandMock->execute($inputMock, $outputMock);

        // Assert
        static::assertSame(0, $output);
    }

    public function getProvidedDataCallNotificationBackend(): array
    {
        return [
            ['sync', 0, 1],
            ['async', 1, 0],
        ];
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
