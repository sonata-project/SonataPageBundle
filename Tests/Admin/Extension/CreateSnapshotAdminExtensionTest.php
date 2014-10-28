<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Admin\Extension;


use Sonata\PageBundle\Admin\Extension\CreateSnapshotAdminExtension;

class CreateSnapshotAdminExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testPostUpdateOnPage()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getId')->will($this->returnValue(42));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            array( 'pageId' => 42)
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postUpdate($admin, $page);
    }

    public function testPostPersistOnPage()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getId')->will($this->returnValue(42));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            array( 'pageId' => 42)
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postPersist($admin, $page);
    }

    public function testPostUpdateOnBlock()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getId')->will($this->returnValue(42));

        $block = $this->getMock('Sonata\PageBundle\Model\PageBlockInterface');
        $block->expects($this->once())->method('getPage')->will($this->returnValue($page));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            array( 'pageId' => 42)
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postUpdate($admin, $block);
    }

    public function testPostPersistOnBlock()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('getId')->will($this->returnValue(42));

        $block = $this->getMock('Sonata\PageBundle\Model\PageBlockInterface');
        $block->expects($this->once())->method('getPage')->will($this->returnValue($page));

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('createAndPublish')->with(
            'sonata.page.create_snapshot',
            array( 'pageId' => 42)
        );

        $extension = new CreateSnapshotAdminExtension($backend);
        $extension->postPersist($admin, $block);
    }
}