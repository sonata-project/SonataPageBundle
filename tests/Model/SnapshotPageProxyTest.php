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

use PHPUnit\Framework\TestCase;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Model\SnapshotPageProxyInterface;
use Sonata\PageBundle\Model\TransformerInterface;

class SnapshotPageProxyTest extends TestCase
{
    public function testInterface(): void
    {
        static::assertInstanceOf(
            SnapshotPageProxyInterface::class,
            new SnapshotPageProxy(
                $this->createMock(SnapshotManagerInterface::class),
                $this->createMock(TransformerInterface::class),
                $this->createMock(SnapshotInterface::class)
            )
        );
    }
}
