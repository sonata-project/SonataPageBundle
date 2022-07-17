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

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Command\CreateBlockContainerCommand;
use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Tests\Model\Page;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateBlockContainerCommandTest extends TestCase
{
    /**
     * Tests that Block is added into Page's blocks field.
     */
    public function testCreateBlock(): void
    {
        //Mock
        $pageManagerMock = $this->createStub(PageManagerInterface::class);
        $blockInteractorMock = $this->createStub(BlockInteractorInterface::class);

        $block = $this->createStub(PageBlockInterface::class);
        $blockInteractorMock->method('createNewContainer')->willReturn($block);

        $page = new Page();
        $pageManagerMock->method('findBy')->with(['templateCode' => 'foo'])->willReturn([$page]);
        $pageManagerMock->method('save')->with($page)->willReturn($page);

        $command = new CreateBlockContainerCommand($pageManagerMock, $blockInteractorMock);

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
