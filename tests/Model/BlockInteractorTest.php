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

namespace Sonata\PageBundle\Tests\Model;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Entity\BlockInteractor;
use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Tests\App\Entity\SonataPageBlock;

/**
 * @author Vincent Composieux <composieux@ekino.com>
 */
final class BlockInteractorTest extends TestCase
{
    public function testCreateNewContainer(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $blockManager = $this->createMock(BlockManagerInterface::class);
        $blockManager->method('create')->willReturn(new SonataPageBlock());

        $blockInteractor = new BlockInteractor($registry, $blockManager);

        $container = $blockInteractor->createNewContainer([
            'enabled' => true,
            'code' => 'my-code',
        ]);

        static::assertInstanceOf(PageBlockInterface::class, $container);

        $settings = $container->getSettings();

        static::assertTrue($container->getEnabled());

        static::assertSame('my-code', $settings['code']);
    }
}
