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
    public function setUp()
    {
        $this->snapshotManager = $this->getMock('Sonata\PageBundle\Model\SnapshotManagerInterface');
        $this->snapshot = $this->getMock(
            'Sonata\PageBundle\Model\Snapshot',
            array('getContent')
        );
        $transformer  = $this->getMock('Sonata\PageBundle\Model\TransformerInterface');

        $this->proxy = new SnapshotPageProxy($this->snapshotManager, $transformer, $this->snapshot);
    }

    public function testGetTarget()
    {
        $this->snapshot->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue(array('target_id' => false)));

        $this->snapshotManager->expects($this->exactly(0))
            ->method('findEnableSnapshot');


        $this->assertNull($this->proxy->getTarget());
    }
}
