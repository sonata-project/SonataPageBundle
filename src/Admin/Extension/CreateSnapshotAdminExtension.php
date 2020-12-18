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
use Sonata\PageBundle\Publisher\Publisher;

class CreateSnapshotAdminExtension extends AbstractAdminExtension
{
    /**
     * @var BackendInterface
     */
    protected $backend;

    /**
     * @var Publisher
     */
    private $publisher;

    /**
     * @param Publisher|BackendInterface $publisherOrBackend
     */
    public function __construct(object $publisherOrBackend)
    {
        if ($publisherOrBackend instanceof Publisher) {
            $this->publisher = $publisherOrBackend;
        } elseif ($publisherOrBackend instanceof BackendInterface) {
            @trigger_error(sprintf(
                'Passing %s as argument 1 to %s() is deprecated since sonata-project/page-bundle 3.x'
                .' and will throw a \TypeError in version 4.0. You must pass an instance of %s instead.',
                BackendInterface::class,
                __METHOD__,
                Publisher::class
            ), E_USER_DEPRECATED);

            $this->backend = $publisherOrBackend;
        } else {
            throw new TypeError(sprintf(
                'Argument 1 passed to %s() must be either null or an instance of %s or preferably %s, instance of %s given.',
                __METHOD__,
                BackendInterface::class,
                Publisher::class,
                \get_class($publisherOrBackend)
            ));
        }
    }

    public function postUpdate(AdminInterface $admin, $object)
    {
        $this->sendMessage($object);
    }

    public function postPersist(AdminInterface $admin, $object)
    {
        $this->sendMessage($object);
    }

    /**
     * @param PageInterface $object
     */
    protected function sendMessage($object)
    {
        if ($object instanceof BlockInterface && method_exists($object, 'getPage')) {
            $page = $object->getPage();
        } elseif ($object instanceof PageInterface) {
            $page = $object;
        } else {
            return;
        }

        if (null !== $this->publisher) {
            $this->publisher->createSnapshot($page);

            return;
        }

        $this->backend->createAndPublish('sonata.page.create_snapshot', [
            'pageId' => $page->getId(),
        ]);
    }
}
