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

use PHPUnit\Framework\TestCase;
use Sonata\BlockBundle\Block\BlockServiceManagerInterface;
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\PageBundle\Admin\BaseBlockAdmin;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;

class BaseBlockAdminTest extends TestCase
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
        $idx = [];
        $blockAdmin->preBatchAction('delete', $query, $idx, true);
    }

    public function testSettingAsEditedOnPreRemove()
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects($this->once())->method('setEdited')->with(true);

        $block = $this->createMock(PageBlockInterface::class);
        $block->expects($this->once())->method('getPage')->will($this->returnValue($page));

        $blockService = $this->createMock(AbstractAdminBlockService::class);
        $blockService->expects($this->any())->method('preRemove')->with($block);

        $blockServiceManager = $this->createMock(BlockServiceManagerInterface::class);
        $blockServiceManager->expects($this->once())->method('get')->with($block)->will($this->returnValue($blockService));

        $blockAdmin = $this->getMockBuilder(BaseBlockAdmin::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $blockAdmin->setBlockManager($blockServiceManager);
        $blockAdmin->preRemove($block);
    }
}
