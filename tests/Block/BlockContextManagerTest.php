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
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\Block;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class BlockContextManagerTest extends TestCase
{
    public function testGetWithValidData(): void
    {
        $settings = [
            'use_cache' => true,
            'extra_cache_keys' => [],
            'attr' => [],
            'template' => 'test_template',
            'ttl' => 0,
            'manager' => false,
            'page_id' => false,
        ];
        $block = new Block();
        $block->setSettings($settings);
        $blockContext = new BlockContext($block, $block->getSettings());

        static::assertInstanceOf(BlockContextInterface::class, $blockContext);
        static::assertSame($settings, $blockContext->getSettings());
        static::assertSame('test_template', $blockContext->getTemplate());
    }
}
