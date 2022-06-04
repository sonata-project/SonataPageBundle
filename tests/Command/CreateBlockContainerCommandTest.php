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

use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\MessageManagerBackend;
use Sonata\NotificationBundle\Backend\RuntimeBackend;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\Command\CreateBlockContainerCommand;
use Sonata\PageBundle\Entity\BlockInteractor;
use Sonata\PageBundle\Entity\BlockManager;
use Sonata\PageBundle\Entity\PageManager;
use Sonata\PageBundle\Entity\SiteManager;
use Sonata\PageBundle\Entity\SnapshotManager;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateBlockContainerCommandTest extends TestCase
{
    /** @var Stub&BlockInteractor */
    protected $blockInteractor;

    /** @var Stub&PageManager */
    protected $pageManager;

    /** @var Stub&SnapshotManager */
    protected $snapshotManager;

    /** @var Stub&BlockManager */
    protected $blockManager;

    /** @var Stub&SiteManager */
    protected $siteManager;

    /** @var Stub&CmsPageManager */
    protected $cmsPageManager;

    /** @var Stub&ExceptionListener */
    protected $exceptionListener;

    /** @var Stub&MessageManagerBackend */
    protected $backend;

    /** @var Stub&RuntimeBackend */
    protected $backendRuntime;

    protected function setUp(): void
    {
        $this->blockInteractor = $this->createStub(BlockInteractor::class);
        $this->pageManager = $this->createStub(PageManager::class);
        $this->snapshotManager = $this->createStub(SnapshotManager::class);
        $this->blockManager = $this->createStub(BlockManager::class);
        $this->siteManager = $this->createStub(SiteManager::class);
        $this->cmsPageManager = $this->createStub(CmsPageManager::class);
        $this->exceptionListener = $this->createStub(ExceptionListener::class);
        $this->backend = $this->createStub(MessageManagerBackend::class);
        $this->backendRuntime = $this->createStub(RuntimeBackend::class);
    }

    /**
     * Tests that Block is added into Page's blocks field.
     */
    public function testCreateBlock(): void
    {
        $block = $this->createStub(PageBlockInterface::class);
        $this->blockInteractor->method('createNewContainer')->willReturn($block);

        $page = new Page();
        $this->pageManager->method('findBy')->with(['templateCode' => 'foo'])->willReturn([$page]);
        $this->pageManager->method('save')->with($page)->willReturn($page);

        $command = new CreateBlockContainerCommand(
            $this->siteManager,
            $this->pageManager,
            $this->snapshotManager,
            $this->blockManager,
            $this->cmsPageManager,
            $this->exceptionListener,
            $this->backend,
            $this->backendRuntime,
            $this->blockInteractor
        );

        $input = $this->createStub(InputInterface::class);
        $input->method('getArgument')->willReturnMap([
            ['templateCode', 'foo'],
            ['blockCode', 'content_bar'],
            ['blockName', 'Baz!'],
        ]);

        $output = $this->createStub(OutputInterface::class);

        $method = new \ReflectionMethod($command, 'execute');
        $method->setAccessible(true);
        $method->invoke($command, $input, $output);

        static::assertSame($page->getBlocks(), [$block]);
    }
}
