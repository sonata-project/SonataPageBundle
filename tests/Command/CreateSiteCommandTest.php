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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\NotificationBundle\Backend\MessageManagerBackend;
use Sonata\NotificationBundle\Backend\RuntimeBackend;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\Command\CreateSiteCommand;
use Sonata\PageBundle\Entity\BlockInteractor;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Tests\Model\Site;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
final class CreateSiteCommandTest extends TestCase
{
    /** @var Stub&BlockInteractor */
    protected $blockInteractor;

    /** @var Stub&SnapshotManager */
    protected $snapshotManager;

    /** @var Stub&CmsPageManager */
    protected $cmsPageManager;

    /** @var Stub&ExceptionListener */
    protected $exceptionListener;

    /** @var Stub&MessageManagerBackend */
    protected $backend;

    /** @var Stub&RuntimeBackend */
    protected $backendRuntime;

    /** @var Application */
    private $application;

    /** @var MockObject&SiteManagerInterface */
    private $siteManager;

    /** @var MockObject&PageManagerInterface */
    private $pageManager;

    /** @var MockObject&BlockManagerInterface */
    private $blockManager;

    protected function setUp(): void
    {
        $this->siteManager = $this->createMock(SiteManagerInterface::class);
        $this->pageManager = $this->createMock(PageManagerInterface::class);
        $this->blockManager = $this->createMock(BlockManagerInterface::class);
        $this->blockInteractor = $this->createStub(BlockInteractor::class);
        $this->snapshotManager = $this->createStub(SnapshotManager::class);
        $this->cmsPageManager = $this->createStub(CmsPageManager::class);
        $this->exceptionListener = $this->createStub(ExceptionListener::class);
        $this->backend = $this->createStub(MessageManagerBackend::class);
        $this->backendRuntime = $this->createStub(RuntimeBackend::class);

        $command = new CreateSiteCommand(
            $this->siteManager,
            $this->pageManager,
            $this->snapshotManager,
            $this->blockManager,
            $this->cmsPageManager,
            $this->exceptionListener,
            $this->backend,
            $this->backendRuntime
        );

        $this->application = new Application();
        $this->application->add($command);
    }

    public function testExecuteWithNoConfirmation(): void
    {
        $site = new Site();

        $this->siteManager->method('create')->willReturn($site);
        $this->siteManager->expects(static::once())->method('save')->with($site);

        $command = $this->application->find('sonata:page:create-site');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--name' => 'foo',
            '--host' => 'foo',
            '--relativePath' => 'foo',
            '--enabled' => true,
            '--enabledFrom' => 'now',
            '--enabledTo' => 'now',
            '--default' => true,
            '--locale' => 'foo',
            '--no-confirmation' => true,
        ]);

        static::assertMatchesRegularExpression('@Site created !@', $commandTester->getDisplay());
    }

    public function testExecuteWithoutNoConfirmation(): void
    {
        $site = new Site();

        $this->siteManager->method('create')->willReturn($site);
        $this->siteManager->expects(static::never())->method('save')->with($site);

        $questionHelper = $this->createStub(QuestionHelper::class);
        $questionHelper->method('getName')->willReturn('question');
        $questionHelper->method('ask')->willReturn(false);
        $questionHelper->method('setHelperSet')->willReturn(true);

        $command = $this->application->find('sonata:page:create-site');
        $command->setHelperSet(new HelperSet([$questionHelper]));

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--name' => 'foo',
            '--host' => 'foo',
            '--relativePath' => 'foo',
            '--enabled' => true,
            '--enabledFrom' => 'now',
            '--enabledTo' => 'now',
            '--default' => true,
            '--locale' => 'foo',
        ]);

        static::assertMatchesRegularExpression('@Site creation cancelled !@', $commandTester->getDisplay());
    }
}
