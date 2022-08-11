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

use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Model\TransformerInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotByPageInterface;
use Sonata\PageBundle\Service\Contract\CreateSnapshotBySiteInterface;

final class CreateSnapshotService implements CreateSnapshotBySiteInterface, CreateSnapshotByPageInterface
{
    private SnapshotManagerInterface $snapshotManager;

    private PageManagerInterface $pageManager;

    private TransformerInterface $transformer;

    public function __construct(
        SnapshotManagerInterface $snapshotManager,
        PageManagerInterface $pageManager,
        TransformerInterface $transformer
    ) {
        $this->snapshotManager = $snapshotManager;
        $this->pageManager = $pageManager;
        $this->transformer = $transformer;
    }

    public function createBySite(SiteInterface $site): void
    {
        $pages = $this->pageManager->findBy(['site' => $site->getId()]);

        if ($this->snapshotManager instanceof BaseEntityManager) {
            $this->snapshotManager->getEntityManager()->beginTransaction();
        }

        foreach ($pages as $page) {
            $this->createByPage($page);
        }

        if ($this->snapshotManager instanceof BaseEntityManager) {
            $this->snapshotManager->getEntityManager()->commit();
        }
    }

    public function createByPage(PageInterface $page): SnapshotInterface
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
