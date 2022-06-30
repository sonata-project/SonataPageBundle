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
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotByPageInterface;

/**
 * @final since sonata-project/page-bundle 3.26
 */
class CreateSnapshotAdminExtension extends AbstractAdminExtension
{
    /**
     * @var BackendInterface|CreateSnapshotByPageInterface
     *                                                     NEXT_MAJOR: rename this variable, for example: createSnapshotByPage
     *
     * @deprecated since 3.27, and it will be removed in 4.0.
     */
    protected $backend;

    public function __construct($backend)
    {
        $this->backend = $backend;
    }

    public function postUpdate(AdminInterface $admin, $object)
    {
        $this->sendMessage($object);
    }

    public function postPersist(AdminInterface $admin, $object)
    {
        $this->sendMessage($object);
    }

    public function postRemove(AdminInterface $admin, $object)
    {
        $this->sendMessage($object);
    }

    /**
     * @param PageInterface $object
     *
     * @deprecated since 3.27, and it will be removed in 4.0.
     * NEXT_MAJOR: rename this method, as we are not using message code, no make sense keep this name.
     */
    protected function sendMessage($object)
    {
        if ($object instanceof BlockInterface && method_exists($object, 'getPage')) {
            $page = $object->getPage();
            $pageId = $page->getId(); //NEXT_MAJOR: Remove this line.
        } elseif ($object instanceof PageInterface) {
            $page = $object;
            $pageId = $page->getId(); //NEXT_MAJOR: Remove this line.
        } else {
            return;
        }

        //NEXT_MAJOR: Remove the if code and all code related with BackendInterface
        if ($this->backend instanceof BackendInterface) {
            @trigger_error(
                sprintf(
                    'Inject %s in %s is deprecated since sonata-project/page-bundle 3.27.0'.
                    ' and will be removed in 4.0, Please inject %s insteadof %s',
                    BackendInterface::class,
                    self::class,
                    CreateSnapshotByPageInterface::class,
                    BackendInterface::class
                ),
                \E_USER_DEPRECATED
            );
            $this->backend->createAndPublish('sonata.page.create_snapshot', [
                'pageId' => $pageId,
            ]);
        } else {
            $this->backend->createByPage($page);
        }
    }
}
