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
 * Defines methods to interact with the persistency layer of a SnapshotInterface.
 *
 * @extends ManagerInterface<SnapshotInterface>
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SnapshotManagerInterface extends ManagerInterface
{
    /**
     * @return SnapshotInterface
     */
    public function findEnableSnapshot(array $criteria);

    /**
     * @param array          $snapshots A snapshots array to enable
     * @param \DateTime|null $date      A date instance
     */
    public function enableSnapshots(array $snapshots, ?\DateTime $date = null);

    public function createSnapshotPageProxy(TransformerInterface $transformer, SnapshotInterface $snapshot): SnapshotPageProxyInterface;

    /**
     * Cleanups the deprecated snapshots.
     *
     * @param PageInterface $page A page instance
     * @param int           $keep Number of snapshots to keep
     *
     * @return int The number of deleted rows
     */
    public function cleanup(PageInterface $page, $keep);
}
