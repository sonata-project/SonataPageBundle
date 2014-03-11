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

use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;

use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\PageManagerInterface;

/**
 * Consumer class to cleanup snapshots by a given page
 */
class CleanupSnapshotConsumer implements ConsumerInterface
{
    /**
     * @var SnapshotManagerInterface
     */
    protected $snapshotManager;

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * Constructor
     *
     * @param SnapshotManagerInterface $snapshotManager A snapshot manager instance
     * @param PageManagerInterface     $pageManager     A page manager instance
     */
    public function __construct(SnapshotManagerInterface $snapshotManager, PageManagerInterface $pageManager)
    {
        $this->snapshotManager = $snapshotManager;
        $this->pageManager     = $pageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $page = $this->pageManager->findOneBy(array(
            'id' => $event->getMessage()->getValue('pageId')
        ));

        if (!$page) {
            return;
        }

        // start a transaction
        $this->snapshotManager->getConnection()->beginTransaction();

        // cleanup snapshots
        $this->snapshotManager->cleanup($page, $event->getMessage()->getValue('keepSnapshots'));

        // commit the changes
        $this->snapshotManager->getConnection()->commit();
    }
}