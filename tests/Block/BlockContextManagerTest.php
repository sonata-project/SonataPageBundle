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
final class BlockContextManagerTest extends TestCase
{
    public function testGetWithValidData(): void
    {
        $service = $this->createMock(AbstractBlockService::class);

        $blockLoader = $this->createMock(BlockLoaderInterface::class);

        $serviceManager = $this->createMock(BlockServiceManagerInterface::class);
        $serviceManager->expects(static::once())->method('get')->willReturn($service);

        $block = $this->createMock(BlockInterface::class);
        $block->expects(static::once())->method('getSettings')->willReturn([]);

        $manager = new BlockContextManager($blockLoader, $serviceManager);

        $blockContext = $manager->get($block);

        static::assertInstanceOf(BlockContextInterface::class, $blockContext);

        static::assertSame([
            'use_cache' => true,
            'extra_cache_keys' => [],
            'attr' => [],
            'template' => false,
            'ttl' => 0,
            'manager' => false,
            'page_id' => false,
        ], $blockContext->getSettings());
    }
}
