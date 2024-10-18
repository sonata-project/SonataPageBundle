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

use Sonata\Doctrine\Model\TransactionalManagerInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Sonata\PageBundle\Service\Contract\CleanupSnapshotBySiteInterface;

final class CleanupSnapshotService implements CleanupSnapshotBySiteInterface
{
    public function __construct(
        private SnapshotManagerInterface $snapshotManager,
        private PageManagerInterface $pageManager,
    ) {
    }

    public function cleanupBySite(SiteInterface $site, int $keepSnapshots): void
    {
        $pages = $this->pageManager->findBy(['site' => $site->getId()]);

        if ($this->snapshotManager instanceof TransactionalManagerInterface) {
            $this->snapshotManager->beginTransaction();
        }

        foreach ($pages as $page) {
            $this->snapshotManager->cleanup($page, $keepSnapshots);
        }

        if ($this->snapshotManager instanceof TransactionalManagerInterface) {
            $this->snapshotManager->commit();
        }
    }
}
