<?php

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
use Sonata\PageBundle\Model\SnapshotPageProxy;

class SnapshotPageProxyTest extends TestCase
{
    public function testInterface()
    {
        $snapshotManager = $this->createMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        $snapshot = $this->createMock('Sonata\PageBundle\Model\SnapshotInterface');
        $transformer = $this->createMock('Sonata\PageBundle\Model\TransformerInterface');

        new SnapshotPageProxy($snapshotManager, $transformer, $snapshot);
    }
}
