<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Consumer;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * Consumer class to generate a snapshot.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CreateSnapshotConsumer implements ConsumerInterface
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
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * @param SnapshotManagerInterface $snapshotManager
     * @param PageManagerInterface     $pageManager
     * @param TransformerInterface     $transformer
     */
    public function __construct(SnapshotManagerInterface $snapshotManager, PageManagerInterface $pageManager, TransformerInterface $transformer)
    {
        $this->snapshotManager = $snapshotManager;
        $this->pageManager = $pageManager;
        $this->transformer = $transformer;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $pageId = $event->getMessage()->getValue('pageId');

        $page = $this->pageManager->findOneBy(['id' => $pageId]);

        if (!$page) {
            return;
        }

        // start a transaction
        $this->snapshotManager->getConnection()->beginTransaction();

        // creating snapshot
        $snapshot = $this->transformer->create($page);

        // update the page status
        $page->setEdited(false);
        $this->pageManager->save($page);

        // save the snapshot
        $this->snapshotManager->save($snapshot);
        $this->snapshotManager->enableSnapshots([$snapshot]);

        // commit the changes
        $this->snapshotManager->getConnection()->commit();
    }
}
