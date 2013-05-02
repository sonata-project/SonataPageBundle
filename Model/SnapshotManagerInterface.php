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

/**
 * Defines methods to interact with the persistency layer of a SnapshotInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface SnapshotManagerInterface
{
    /**
     * @param array $criteria
     *
     * @return SnapshotInterface
     */
    function findOneBy(array $criteria);

    /**
     * @param array $criteria
     *
     * @return array
     */
    function findBy(array $criteria);

    /**
     * @param SnapshotInterface $object
     */
    function save(SnapshotInterface $object);

    /**
     * @return SnapshotInterface
     */
    function create();

    /**
     * @param array $criteria
     *
     * @return SnapshotInterface
     */
    function findEnableSnapshot(array $criteria);

    /**
     * @param array $snapshots
     */
    function enableSnapshots(array $snapshots);

    /**
     * @return string
     */
    function getClass();

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