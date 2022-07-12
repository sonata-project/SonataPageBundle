<?php

namespace Sonata\PageBundle\Tests\Command;

use InvalidArgumentException;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Service\Contract\GetSitesFromCommandInterface;
use Sonata\PageBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CleanupSnapshotsCommandTest extends KernelTestCase
{
    private $application;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup SymfonyKernel
        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
    }

    public function testRequireSiteOption(): void
    {
        //Command
        $command = $this->application->find('sonata:page:cleanup-snapshots');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('Please provide an --site=SITE_ID option or the --site=all directive', $output);
    }

    /**
     * @testdox It's checking if the mode are "sync" or "async".
     * @group legacy
     *
     * NEXT_MAJOR: Remove this test.
     */
    public function testInvalidSiteModeValue(): void
    {
        //Assert
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Option "mode" is not valid (async|sync).');

        //Setup command
        $command = $this->application->find('sonata:page:cleanup-snapshots');
        $commandTester = new CommandTester($command);

        //Run code
        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => [1],
            '--mode' => 'wrongValue',
        ]);
    }

    /**
     * @testdox It's checking if the "Keep-snapshots" option is a number.
     */
    public function testKeepSnapshotIsANumberValue(): void
    {
        //Assert
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Please provide an integer value for the option "keep-snapshots".');

        //Setup command
        $command = $this->application->find('sonata:page:cleanup-snapshots');
        $commandTester = new CommandTester($command);

        //Run code
        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => [1],
            '--mode' => 'sync',//NEXT_MAJOR: Remove this argument.
            '--keep-snapshots' => '5a',
        ]);
    }

    /**
     * @test it's cleanup for all sites using notification bundle.
     * @group legacy
     */
    public function testCleanupSnapshotAsync(): void
    {
        //Mock
        $getSitesMock = $this->createMock(GetSitesFromCommandInterface::class);
        $getSitesMock
            ->method('findSitesById')
            ->willReturn([$this->createMock(Site::class)]);
        self::$container->set('sonata.page.service.get_sites', $getSitesMock);

        $notificationBackend = $this->createMock(BackendInterface::class);
        $notificationBackend
            ->expects(static::once())
            ->method('createAndPublish');
        self::$container->set('sonata.notification.backend', $notificationBackend);

        //Setup command
        $command = $this->application->find('sonata:page:cleanup-snapshots');
        $commandTester = new CommandTester($command);

        //Run code
        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => ['all'],
            '--mode' => 'async',//NEXT_MAJOR: Remove this argument.
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('- Publish a notification command ...', $output);
        static::assertStringContainsString('done!', $output);
    }

    /**
     * @testdox It's cleanup snapshots.
     */
    public function testCleanupSnapshot(): void
    {
        //Mock
        $getSitesMock = $this->createMock(GetSitesFromCommandInterface::class);
        $getSitesMock
            ->method('findSitesById')
            ->willReturn([$this->createMock(Site::class)]);
        self::$container->set('sonata.page.service.get_sites', $getSitesMock);

        //Setup command
        $command = $this->application->find('sonata:page:cleanup-snapshots');
        $commandTester = new CommandTester($command);

        //Run code
        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => ['all'],
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringNotContainsString('- Publish a notification command ...', $output);
        static::assertStringContainsString('- Cleaning up snapshots ...', $output);
        static::assertStringContainsString('done!', $output);
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}