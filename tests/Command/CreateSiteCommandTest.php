<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\Test\PageBundle\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sonata\PageBundle\Command\CreateSiteCommand;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Tests\Model\Site;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Ahmet Akbana <ahmetakbana@gmail.com>
 */
class CreateSiteCommandTest extends TestCase
{
    /**
     * @var Application|ObjectProphecy
     */
    private $application;

    /**
     * @var SiteManagerInterface|ObjectProphecy
     */
    private $siteManager;

    protected function setUp()
    {
        $this->siteManager = $this->prophesize(SiteManagerInterface::class);

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('sonata.page.manager.site')->willReturn($this->siteManager->reveal());

        $command = new CreateSiteCommand();
        $command->setContainer($container->reveal());

        $this->application = new Application();
        $this->application->add($command);
    }

    public function testExecuteWithNoConfirmation()
    {
        $site = new Site();

        $this->siteManager->create()->willReturn($site);
        $this->siteManager->save($site)->shouldBeCalled();

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

        $this->assertRegExp('@Site created !@', $commandTester->getDisplay());
    }

    public function testExecuteWithoutNoConfirmation()
    {
        $site = new Site();

        $this->siteManager->create()->willReturn($site);
        $this->siteManager->save($site)->shouldNotbeCalled();

        $questionHelper = $this->prophesize(QuestionHelper::class);
        $questionHelper->getName()->willReturn('question');
        $questionHelper->ask(Argument::any(), Argument::any(), Argument::any())->willReturn(false);
        $questionHelper->setHelperSet(Argument::any())->willReturn(true);

        $command = $this->application->find('sonata:page:create-site');
        $command->setHelperSet(new HelperSet([$questionHelper->reveal()]));

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

        $this->assertRegExp('@Site creation cancelled !@', $commandTester->getDisplay());
    }
}
