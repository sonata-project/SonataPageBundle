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
use Prophecy\Prophecy\ObjectProphecy;
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Command\CloneSiteCommand;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Christian Gripp <mail@core23.de>
 */
class CloneSiteCommandTest extends TestCase
{
    /**
     * @var Application|ObjectProphecy
     */
    private $application;

    /**
     * @var SiteManagerInterface|ObjectProphecy
     */
    private $siteManager;

    /**
     * @var PageManagerInterface|ObjectProphecy
     */
    private $pageManager;

    /**
     * @var BlockManagerInterface|ObjectProphecy
     */
    private $blockManager;

    protected function setUp()
    {
        $this->siteManager = $this->prophesize('Sonata\PageBundle\Model\SiteManagerInterface');
        $this->pageManager = $this->prophesize('Sonata\PageBundle\Model\PageManagerInterface');
        $this->blockManager = $this->prophesize('Sonata\BlockBundle\Model\BlockManagerInterface');

        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->get('sonata.page.manager.site')->willReturn($this->siteManager->reveal());
        $container->get('sonata.page.manager.page')->willReturn($this->pageManager->reveal());
        $container->get('sonata.page.manager.block')->willReturn($this->blockManager->reveal());

        $command = new CloneSiteCommand();
        $command->setContainer($container->reveal());

        $this->application = new Application();
        $this->application->add($command);
    }

    public function testExecute()
    {
        $sourceSite = $this->prophesize('Sonata\PageBundle\Model\SiteInterface');
        $destSite = $this->prophesize('Sonata\PageBundle\Model\SiteInterface');

        $this->siteManager->find(23)->willReturn($sourceSite->reveal());
        $this->siteManager->find(42)->willReturn($destSite->reveal());

        $page1 = $this->prophesize('Sonata\PageBundle\Model\PageInterface');
        $page1->getId()->willReturn(1);
        $page1->getTitle()->willReturn('Page 1');
        $page1->getParent()->willReturn(null);
        $page1->isHybrid()->willReturn(true);

        $page2 = $this->prophesize('Sonata\PageBundle\Model\PageInterface');
        $page2->getId()->willReturn(2);
        $page2->getTitle()->willReturn('Page 2');
        $page2->getParent()->willReturn($page1->reveal());
        $page2->getTarget()->willReturn(null);
        $page2->isHybrid()->willReturn(true);

        $page1->getTarget()->willReturn($page2->reveal());

        $page3 = $this->prophesize('Sonata\PageBundle\Model\PageInterface');
        $page3->getId()->willReturn(3);
        $page3->isHybrid()->willReturn(false);

        $this->pageManager->findBy([
            'site' => $sourceSite,
        ])->willReturn([
            $page1->reveal(),
            $page2->reveal(),
            $page3->reveal(),
        ]);

        // Replace this with new mock, when cloing is supported in prophecies
        $newPage1 = $page1;
        $newPage1->setTitle('Copy of Page 1')->shouldBeCalled();
        $newPage1->setSite($destSite)->shouldBeCalled();

        $this->pageManager->save($newPage1)->shouldBeCalled();

        // Replace this with new mock, when cloing is supported in prophecies
        $newPage2 = $page2;
        $newPage2->setTitle('Copy of Page 2')->shouldBeCalled();
        $newPage2->setSite($destSite)->shouldBeCalled();
        $newPage2->setParent($newPage1)->shouldBeCalled();

        $newPage1->setTarget($newPage2)->shouldBeCalled();

        $this->pageManager->save($newPage2)->shouldBeCalled();
        $this->pageManager->save($newPage2, true)->shouldBeCalled();

        $block = $this->prophesize('Sonata\PageBundle\Model\PageBlockInterface');
        $block->getId()->willReturn(4711);
        $block->getParent()->willReturn(null);

        $this->blockManager->findBy([
           'page' => $page1,
        ])->willReturn([]);

        $this->blockManager->findBy([
           'page' => $page2,
        ])->willReturn([
            $block->reveal(),
        ]);

        // Replace this with new mock, when cloing is supported in prophecies
        $newBlock = $block;
        $block->setPage($newPage2)->shouldBeCalled();

        $this->blockManager->save($newBlock)->shouldBeCalled();

        $command = $this->application->find('sonata:page:clone-site');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--source-id' => 23,
            '--dest-id' => 42,
            '--prefix' => 'Copy of ',
            '--only-hybrid' => true,
        ]);

        $this->assertRegExp('@done!@', $commandTester->getDisplay());
    }

    public function testExecuteNoSourceId()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Please provide a "--source-id=SITE_ID" option.');

        $this->siteManager->findAll()->willReturn([]);

        $command = $this->application->find('sonata:page:clone-site');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--dest-id' => 42,
            '--prefix' => 'Copy of ',
        ]);

        $this->assertRegExp('@Writing cache file ...\s+done!@', $commandTester->getDisplay());
    }

    public function testExecuteNoDestId()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Please provide a "--dest-id=SITE_ID" option.');

        $this->siteManager->findAll()->willReturn([]);

        $command = $this->application->find('sonata:page:clone-site');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--source-id' => 23,
            '--prefix' => 'Copy of ',
        ]);

        $this->assertRegExp('@Writing cache file ...\s+done!@', $commandTester->getDisplay());
    }

    public function testExecuteNoPrefix()
    {
        $this->setExpectedException('\InvalidArgumentException', 'Please provide a "--prefix=PREFIX" option.');

        $this->siteManager->findAll()->willReturn([]);

        $command = $this->application->find('sonata:page:clone-site');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--source-id' => 23,
            '--dest-id' => 42,
        ]);
    }
}
