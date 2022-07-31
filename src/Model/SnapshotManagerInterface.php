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

namespace Sonata\PageBundle\Model;

use Sonata\Doctrine\Model\ManagerInterface;

/**
 * @extends ManagerInterface<SnapshotInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SnapshotManagerInterface extends ManagerInterface
{
    /**
     * @param array<string, mixed> $criteria
     *
     * @return SnapshotInterface|null
     */
    public function findEnableSnapshot(array $criteria);

    /**
     * @param array<SnapshotInterface> $snapshots
     *
     * @return void
     */
    public function enableSnapshots(array $snapshots, ?\DateTimeInterface $date = null);

    public function createSnapshotPageProxy(TransformerInterface $transformer, SnapshotInterface $snapshot): SnapshotPageProxyInterface;

    /**
     * Cleanup old snapshots and keep only the $keep number of them.
     * This method returns the number of deleted snapshots.
     *
     * @param int $keep
     *
     * @return int
     */
    public function cleanup(PageInterface $page, $keep);
}
