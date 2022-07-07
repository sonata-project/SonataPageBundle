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

namespace Sonata\PageBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotByPageInterface;

/**
 * @extends AbstractAdminExtension<BlockInterface|PageInterface>
 */
final class CreateSnapshotAdminExtension extends AbstractAdminExtension
{
    protected CreateSnapshotByPageInterface $createSnapshotByPage;

    public function __construct(CreateSnapshotByPageInterface $createSnapshotByPage)
    {
        $this->createSnapshotByPage = $createSnapshotByPage;
    }

    public function postUpdate(AdminInterface $admin, $object): void
    {
        $this->createByPage($object);
    }

    public function postPersist(AdminInterface $admin, $object): void
    {
        $this->createByPage($object);
    }

    public function postRemove(AdminInterface $admin, $object): void
    {
        $this->createByPage($object);
    }

    private function createByPage(object $object): void
    {
        if ($object instanceof BlockInterface && method_exists($object, 'getPage')) {
            $page = $object->getPage();
        } elseif ($object instanceof PageInterface) {
            $page = $object;
        } else {
            return;
        }

        $this->createSnapshotByPage->createByPage($page);
    }
}
