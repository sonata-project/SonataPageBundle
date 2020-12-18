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

namespace Sonata\PageBundle\Publisher;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;

/**
 * @author Christian Gripp <mail@core23.de>
 */
final class SimplePublisher implements Publisher
{
    /**
     * @var PageManagerInterface
     */
    private $pageManager;

    /**
     * @var SnapshotManagerInterface
     */
    private $snapshotManager;

    /**
     * @var TransformerInterface
     */
    private $transformer;

    public function __construct(
        PageManagerInterface $pageManager,
        SnapshotManagerInterface $snapshotManager,
        TransformerInterface $transformer
    ) {
        $this->pageManager = $pageManager;
        $this->snapshotManager = $snapshotManager;
        $this->transformer = $transformer;
    }

    public function createSnapshots(SiteInterface $site): void
    {
        $pages = $this->pageManager->findBy([
            'site' => $site->getId(),
        ]);

        foreach ($pages as $page) {
            $this->createSnapshot($page);
        }
    }

    public function createSnapshot(PageInterface $page): void
    {
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

    public function removeSnapshots(SiteInterface $site, int $keep = 0): void
    {
        $pages = $this->pageManager->findBy([
            'site' => $site->getId(),
        ]);

        foreach ($pages as $page) {
            $this->removeSnapshot($page, $keep);
        }
    }

    public function removeSnapshot(PageInterface $page, int $keep = 0): void
    {
        // start a transaction
        $this->snapshotManager->getConnection()->beginTransaction();

        // cleanup snapshots
        $this->snapshotManager->cleanup($page, $keep);

        // commit the changes
        $this->snapshotManager->getConnection()->commit();
    }
}
