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
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Command\CreateSiteCommand;
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
    /**
     * @var Application
     */
    private $application;

    /**
     * @var MockObject&SiteManagerInterface
     */
    private $siteManager;

    protected function setUp(): void
    {
        $this->siteManager = $this->createMock(SiteManagerInterface::class);

        $command = new CreateSiteCommand($this->siteManager);

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
