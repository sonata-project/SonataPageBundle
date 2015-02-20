<?php
/*
 * This file is part of the Sonata project.
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
 * Defines methods to interact with the persistency layer of a SnapshotInterface
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
    function findEnableSnapshot(array $criteria);

    /**
     * @param array          $snapshots A snapshots array to enable
     * @param \DateTime|null $date      A date instance
     */
    function enableSnapshots(array $snapshots, \DateTime $date = null);

    /**
     * Cleanups the deprecated snapshots
     *
     * @param PageInterface $page A page instance
     * @param integer       $keep Number of snapshots to keep
     *
     * @return integer The number of deleted rows
     */
    function cleanup(PageInterface $page, $keep);
}
