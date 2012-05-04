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
     *
     * @return void
     */
    function save(SnapshotInterface $object);

    /**
     * @param \Sonata\PageBundle\Model\PageInterface $page
     *
     * @return \Sonata\PageBundle\Model\SnapshotInterface
     */
    function create(PageInterface $page);

    /**
     * @param \Sonata\PageBundle\Model\SnapshotInterface $snapshot
     *
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function load(SnapshotInterface $snapshot);

    /**
     * @param \Sonata\PageBundle\Model\PageInterface $page
     *
     * @return array
     */
    function getChildren(PageInterface $page);

    /**
     * @param array $criteria
     *
     * @return \Sonata\PageBundle\Model\SnapshotInterface
     */
    function findEnableSnapshot(array $criteria);

    /**
     * @param array $snapshots
     *
     * @return void
     */
    function enableSnapshots(array $snapshots);
}