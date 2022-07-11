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

namespace Sonata\PageBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\PageBundle\Admin\BaseBlockAdmin;
use Sonata\PageBundle\Model\PageInterface;

final class BaseBlockAdminTest extends TestCase
{
    public function testSettingAsEditedOnPreBatchDeleteAction(): void
    {
        $page = $this->createMock(PageInterface::class);
        $page->expects(static::once())->method('setEdited')->with(true);

        $parent = $this->createMock(AdminInterface::class);
        $parent->expects(static::once())->method('getSubject')->willReturn($page);

        $blockAdmin = $this->getMockBuilder(BaseBlockAdmin::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $blockAdmin->setParent($parent, 'foo');

        $query = $this->createMock(ProxyQueryInterface::class);
        $idx = [];
        $blockAdmin->preBatchAction('delete', $query, $idx, true);
    }
}
