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
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotBySiteInterface;
use Sonata\PageBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * NEXT_MAJOR: Remove this legacy group.
 *
 * @group legacy
 */
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

        $siteManagerMock
            ->method('findAll')
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

    public function testCreateSnapshot(): void
    {
        //Mocks
        $createSnapshotsMock = $this->createMock(CreateSnapshotBySiteInterface::class);
        $createSnapshotsMock
            ->expects(static::once())
            ->method('createBySite')
            ->with(static::isInstanceOf(SiteInterface::class));

        //Set mock services
        self::$container->set('sonata.page.manager.site', $this->siteManagerMock);
        self::$container->set('sonata.page.service.create_snapshot', $createSnapshotsMock);

        //Command
        $command = $this->application->find('sonata:page:create-snapshots');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => ['all'],
            '--mode' => 'sync',
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('done!', $output);
    }

    /**
     * NEXT_MAJOR: remove the dataProvider, because the notification Bundle will be removed and the legacy group.
     *
     * @group legacy
     *
     * @dataProvider getProvidedDataCallNotificationBackend
     */
    public function testCallNotificationBackend(string $mode): void
    {
        // Mocks
        $createSnapshotsMock = $this->createMock(CreateSnapshotBySiteInterface::class);
        $createSnapshotsMock
            ->expects(static::any())
            ->method('createBySite');

        //Set mock services
        self::$container->set('sonata.page.manager.site', $this->siteManagerMock);
        self::$container->set('sonata.page.service.create_snapshot', $createSnapshotsMock);

        //Command
        $command = $this->application->find('sonata:page:create-snapshots');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => ['all'],
            '--mode' => $mode,
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('done!', $output);
    }

    public function getProvidedDataCallNotificationBackend(): array
    {
        return [
            ['sync'],
            ['async'],
        ];
    }

    /**
     * NEXT_MAJOR: Remove this test.
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
