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

use Sonata\PageBundle\Model\SnapshotPageProxy;
use Sonata\PageBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class SnapshotPageProxyTest extends PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $snapshotManager = $this->createMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        $snapshot = $this->createMock('Sonata\PageBundle\Model\SnapshotInterface');
        $transformer = $this->createMock('Sonata\PageBundle\Model\TransformerInterface');

        new SnapshotPageProxy($snapshotManager, $transformer, $snapshot);
    }
}
