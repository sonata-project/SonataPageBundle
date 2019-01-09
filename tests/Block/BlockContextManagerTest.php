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

namespace Sonata\PageBundle\Tests\Block;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\BlockLoaderInterface;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Block\BlockContextManager;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class BlockContextManagerTest extends TestCase
{
    public function testGetWithValidData(): void
    {
        $service = $this->getMockForAbstractClass(AbstractBlockService::class);

        $blockLoader = $this->createMock(BlockLoaderInterface::class);

        $serviceManager = $this->createMock(BlockServiceManagerInterface::class);
        $serviceManager->expects($this->once())->method('get')->will($this->returnValue($service));

        $block = $this->createMock(BlockInterface::class);
        $block->expects($this->once())->method('getSettings')->will($this->returnValue([]));

        $manager = new BlockContextManager($blockLoader, $serviceManager);

        $blockContext = $manager->get($block);

        $this->assertInstanceOf(BlockContextInterface::class, $blockContext);

        $this->assertEquals([
            'manager' => false,
            'page_id' => false,
            'use_cache' => true,
            'extra_cache_keys' => [],
            'attr' => [],
            'template' => false,
            'ttl' => 0,
        ], $blockContext->getSettings());
    }
}
