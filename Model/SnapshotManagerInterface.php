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
    public function findOneBy(array $criteria);

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria);

    /**
     * @param SnapshotInterface $object
     */
    public function save(SnapshotInterface $object);

    /**
     * @return SnapshotInterface
     */
    public function create();

    /**
     * @param array $criteria
     *
     * @return SnapshotInterface
     */
    public function findEnableSnapshot(array $criteria);

    /**
     * @param array $snapshots
     */
    public function enableSnapshots(array $snapshots);

    /**
     * @return string
     */
    public function getClass();
}
