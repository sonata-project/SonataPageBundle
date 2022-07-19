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
use Sonata\PageBundle\Service\Contract\CleanupSnapshotBySiteInterface;
use Sonata\PageBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CleanupSnapshotsCommandTest extends KernelTestCase
{
    private Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
    }

    /**
     * @testdox It's checking if the "Keep-snapshots" option is a number.
     */
    public function testKeepSnapshotIsANumberValue(): void
    {
        //Assert
        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage('Please provide an integer value for the option "keep-snapshots".');

        //Setup command
        $command = $this->application->find('sonata:page:cleanup-snapshots');
        $commandTester = new CommandTester($command);

        //Run code
        $commandTester->execute([
            'command' => $command->getName(),
            '--site' => [1],
            '--keep-snapshots' => '5a',
        ]);
    }

    /**
     * @testdox It's cleanup snapshots.
     */
    public function testCleanupSnapshot(): void
    {
        //Mock
        $siteManagerMock = $this->createMock(SiteManagerInterface::class);
        $siteManagerMock
            ->method('findAll')
            ->willReturn([$this->createMock(SiteInterface::class)]);

        self::$container->set('sonata.page.manager.site', $siteManagerMock);

        $cleanupSnapshotMock = $this->createMock(CleanupSnapshotBySiteInterface::class);
        $cleanupSnapshotMock
            ->expects(static::once())
            ->method('cleanupBySite');

        self::$container->set('sonata.page.service.cleanup_snapshot', $cleanupSnapshotMock);

        //Setup command
        $command = $this->application->find('sonata:page:cleanup-snapshots');
        $commandTester = new CommandTester($command);

        //Run code
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('- Cleaning up snapshots ...', $output);
        static::assertStringContainsString('done!', $output);
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
