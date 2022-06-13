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

namespace Sonata\PageBundle\Service;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;

final class CreateSnapshotsService implements CreateSnapshotsFromSiteInterface
{
    private $snapshotManager;

    private $pageManager;

    private $transformer;

    public function __construct(
        SnapshotManagerInterface $snapshotManager,
        PageManagerInterface $pageManager,
        TransformerInterface $transformer
    ) {
        $this->snapshotManager = $snapshotManager;
        $this->pageManager = $pageManager;
        $this->transformer = $transformer;
    }

    /**
     *
     * @return iterable<SnapshotInterface>
     */
    public function createBySite(SiteInterface $site): iterable
    {
        $entityManager = $this->snapshotManager->getEntityManager();
        $pages = $this->snapshotManager->findBy(['site' => $site->getId()]);

        // start a transaction
        $entityManager->beginTransaction();

        foreach ($pages as $page) {
            yield $this->createByPage($page);
        }

        // commit the changes
        $entityManager->commit();
    }

    private function createByPage(PageInterface $page): SnapshotInterface
    {
        // creating snapshot
        $snapshot = $this->transformer->create($page);

        // update the page status
        $page->setEdited(false);
        $this->pageManager->save($page);

        // save the snapshot
        $this->snapshotManager->save($snapshot);
        $this->snapshotManager->enableSnapshots([$snapshot]);

        return $snapshot;
    }
}
