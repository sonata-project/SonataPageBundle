<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Model;

use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\CoreBundle\Model\PageableManagerInterface;

/**
 * Defines methods to interact with the persistency layer of a SnapshotInterface.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SnapshotManagerInterface extends ManagerInterface, PageableManagerInterface
{
    /**
     * @param array $criteria
     *
     * @return SnapshotInterface
     */
    public function findEnableSnapshot(array $criteria);

    /**
     * @param array          $snapshots A snapshots array to enable
     * @param \DateTime|null $date      A date instance
     */
    public function enableSnapshots(array $snapshots, \DateTime $date = null);

    /**
     * Cleanups the deprecated snapshots.
     *
     * @param PageInterface $page A page instance
     * @param int           $keep Number of snapshots to keep
     *
     * @return int The number of deleted rows
     */
    public function cleanup(PageInterface $page, $keep);

    // NEXT_MAJOR: Uncomment this method
    /*
     * Create snapShotPageProxy instance.
     *
     * @param TransformerInterface $transformer
     * @param SnapshotInterface    $snapshot
     *
     * @return SnapshotPageProxyInterface
     */
    // public function createSnapshotPageProxy(TransformerInterface $transformer, SnapshotInterface $snapshot);
}
