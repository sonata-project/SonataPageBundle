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
use Sonata\PageBundle\Command\CreateBlockContainerCommand;
use Sonata\PageBundle\Entity\BlockInteractor;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class CreateBlockContainerCommandTest extends TestCase
{
    /** @var Stub&BlockInteractor */
    private $blockInteractor;

    /** @var MockObject|ServiceLocator */
    private $locator;

    /** @var MockObject|PageManagerInterface */
    private $pageManager;

    protected function setUp(): void
    {
        $this->locator = $this->createMock(ServiceLocator::class);
        $this->pageManager = $this->createMock(PageManagerInterface::class);
        $this->blockInteractor = $this->createStub(BlockInteractor::class);
    }

    /**
     * Tests that Block is added into Page's blocks field.
     */
    public function testCreateBlock(): void
    {
        $this->locator
            ->method('__invoke')
            ->with('sonata.page.manager.page')
            ->willReturn($this->pageManager);
        $block = $this->createStub(PageBlockInterface::class);
        $this->blockInteractor->method('createNewContainer')->willReturn($block);

        $page = new Page();
        $this->pageManager->method('findBy')->with(['templateCode' => 'foo'])->willReturn([$page]);
        $this->pageManager->method('save')->with($page)->willReturn($page);

        $command = new CreateBlockContainerCommand($this->locator, $this->blockInteractor);

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
