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
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotByPageInterface;

/**
 * @extends AbstractAdminExtension<PageBlockInterface|PageInterface>
 */
final class CreateSnapshotAdminExtension extends AbstractAdminExtension
{
    public function __construct(private CreateSnapshotByPageInterface $createSnapshotByPage)
    {
    }

    public function postUpdate(AdminInterface $admin, object $object): void
    {
        $this->createByPage($object);
    }

    public function postPersist(AdminInterface $admin, object $object): void
    {
        $this->createByPage($object);
    }

    public function postRemove(AdminInterface $admin, object $object): void
    {
        if ($object instanceof PageInterface) {
            return;
        }

        $this->createByPage($object);
    }

    private function createByPage(object $object): void
    {
        if ($object instanceof PageBlockInterface) {
            $object = $object->getPage();
        }

        if (!$object instanceof PageInterface) {
            return;
        }

        $this->createSnapshotByPage->createByPage($object);
    }
}
