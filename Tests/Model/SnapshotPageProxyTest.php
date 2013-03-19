<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Entity;

use Sonata\PageBundle\Model\SnapshotPageProxy;

/**
 *
 */
class SnapshotPageProxyTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $snapshotManager = $this->getMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        $snapshot = $this->getMock('Sonata\PageBundle\Model\SnapshotInterface');
        $transformer  = $this->getMock('Sonata\PageBundle\Model\TransformerInterface');

        new SnapshotPageProxy($snapshotManager, $transformer, $snapshot);
    }
}
