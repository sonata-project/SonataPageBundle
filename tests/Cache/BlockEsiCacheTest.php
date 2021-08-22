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

namespace Sonata\PageBundle\Tests\Page;

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Sonata\Cache\CacheElement;
use Sonata\PageBundle\Cache\BlockEsiCache;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\Routing\RouterInterface;

class BlockEsiCacheTest extends TestCase
{
    /**
     * @dataProvider getExceptionCacheKeys
     */
    public function testExceptions($keys): void
    {
        $this->expectException(\RuntimeException::class);

        $router = $this->createMock(RouterInterface::class);
        $resolver = $this->createMock(ControllerResolverInterface::class);
        $argumentResolver = $this->createMock(ArgumentResolverInterface::class);
        $blockRenderer = $this->createMock(BlockRendererInterface::class);
        $contextManager = $this->createMock(BlockContextManagerInterface::class);

        $cache = new BlockEsiCache(
            'My Token',
            [],
            $router,
            'ban',
            $resolver,
            $argumentResolver,
            $blockRenderer,
            $contextManager
        );

        $cache->get($keys);
    }

    public static function getExceptionCacheKeys(): array
    {
        return [
            [[]],
            [['block_id' => 7]],
            [['block_id' => 7, 'page_id' => 8]],
            [['block_id' => 7, 'manager' => 8]],
            [['manager' => 7, 'page_id' => 8]],
            [['manager' => 7, 'page_id' => 8]],
            [['updated_at' => 'foo']],
        ];
    }

    public function testInitCache(): void
    {
        $router = $this->createMock(RouterInterface::class);
        $router
            ->method('generate')
            ->willReturn('https://sonata-project.org/cache/XXX/page/esi/page/5/4?updated_at=as');

        $resolver = $this->createMock(ControllerResolverInterface::class);
        $argumentResolver = $this->createMock(ArgumentResolverInterface::class);
        $blockRenderer = $this->createMock(BlockRendererInterface::class);
        $contextManager = $this->createMock(BlockContextManagerInterface::class);

        $cache = new BlockEsiCache(
            'My Token',
            [],
            $router,
            'ban',
            $resolver,
            $argumentResolver,
            $blockRenderer,
            $contextManager
        );

        $this->assertTrue($cache->flush([]));
        $this->assertTrue($cache->flushAll());

        $keys = [
            'block_id' => 4,
            'page_id' => 5,
            'updated_at' => 'as',
            'manager' => 'page',
        ];

        $cacheElement = $cache->set($keys, 'data');

        $this->assertInstanceOf(CacheElement::class, $cacheElement);

        $this->assertTrue($cache->has(['id' => 7]));

        $cacheElement = $cache->get($keys);

        $this->assertInstanceOf(CacheElement::class, $cacheElement);

        $this->assertSame(
            '<esi:include src="https://sonata-project.org/cache/XXX/page/esi/page/5/4?updated_at=as" />',
            $cacheElement->getData()->getContent()
        );
    }
}
