<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Admin;

use Sonata\PageBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class BaseBlockAdminTest extends PHPUnit_Framework_TestCase
{
    public function testSettingAsEditedOnPreBatchDeleteAction()
    {
        $page = $this->createMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('setEdited')->with(true);

        $parent = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $parent->expects($this->once())->method('getSubject')->will($this->returnValue($page));

        $blockAdmin = $this->getMockBuilder('Sonata\PageBundle\Admin\BaseBlockAdmin')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $blockAdmin->setParent($parent);

        $query = $this->createMock('Sonata\AdminBundle\Datagrid\ProxyQueryInterface');
        $idx = array();
        $blockAdmin->preBatchAction('delete', $query, $idx, true);
    }

    public function testSettingAsEditedOnPreRemove()
    {
        $page = $this->getMock('Sonata\PageBundle\Model\PageInterface');
        $page->expects($this->once())->method('setEdited')->with(true);

        $block = $this->getMock('Sonata\PageBundle\Model\PageBlockInterface');
        $block->expects($this->once())->method('getPage')->will($this->returnValue($page));

        $blockService = $this->getMockBuilder('Sonata\BlockBundle\Block\Service\AbstractAdminBlockService')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $blockService->expects($this->any())->method('preRemove')->with($block);

        $blockServiceManager = $this->getMock('Sonata\BlockBundle\Block\BlockServiceManagerInterface');
        $blockServiceManager->expects($this->once())->method('get')->with($block)->will($this->returnValue($blockService));

        $blockAdmin = $this->getMockBuilder('Sonata\PageBundle\Admin\BaseBlockAdmin')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $blockAdmin->setBlockManager($blockServiceManager);
        $blockAdmin->preRemove($block);
    }
}
