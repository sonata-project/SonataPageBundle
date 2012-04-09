<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Consumer;

use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\PageManagerInterface;

use Sonata\NotificationBundle\Exception\InvalidParameterException;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;

class CreateSnapshotsConsumer implements ConsumerInterface
{
    protected $asyncBackend;

    protected $runtimeBackend;

    protected $pageInterface;

    /**
     * @param \Sonata\NotificationBundle\Backend\BackendInterface $asyncBackend
     * @param \Sonata\NotificationBundle\Backend\BackendInterface $runtimeBackend
     * @param \Sonata\PageBundle\Model\PageManagerInterface $pageManager
     */
    public function __construct(BackendInterface $asyncBackend, BackendInterface $runtimeBackend, PageManagerInterface $pageManager)
    {
        $this->asyncBackend = $asyncBackend;
        $this->runtimeBackend = $runtimeBackend;
        $this->pageManager = $pageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $pages = $this->pageManager->findBy(array(
            'site' => $event->getMessage()->getValue('siteId'),
        ));

        $backend = $event->getMessage()->getValue('mode') == 'async' ? $this->asyncBackend : $this->runtimeBackend;

        foreach ($pages as $page) {
            $backend->createAndPublish('sonata.page.create_snapshot', array(
                'pageId' => $page->getId()
            ));
        }
    }
}