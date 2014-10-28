<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Admin\Extension;


use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\Model\PageInterface;

class CreateSnapshotAdminExtension extends AdminExtension
{
    protected $backend;

    /**
     * @param BackendInterface $backend
     */
    public function __construct(BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    /**
     * @param PageInterface $page
     */
    protected function sendMessage($object)
    {
        if ($object instanceof BlockInterface && method_exists($object, 'getPage')) {
            $pageId = $object->getPage()->getId();
        } else if($object instanceof PageInterface) {
            $pageId = $object->getId();
        } else {
            return;
        }

        $this->backend->createAndPublish('sonata.page.create_snapshot', array(
            'pageId' => $pageId
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(AdminInterface $admin, $object)
    {
        $this->sendMessage($object);
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(AdminInterface $admin, $object)
    {
        $this->sendMessage($object);
    }
}