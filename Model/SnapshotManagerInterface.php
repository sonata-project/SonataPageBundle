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
     * @abstract
     * @param array $criteria
     * @return SnapshotInterface
     */
    function findOneBy(array $criteria = array());

    /**
     * @abstract
     * @param array $criteria
     * @return array
     */
    function findBy(array $criteria = array());

    /**
     * @abstract
     * @param SnapshotInterface $object
     * @return void
     */
    function save(SnapshotInterface $object);

    /**
     * @abstract
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return \Sonata\PageBundle\Model\SnapshotInterface
     */
    function create(PageInterface $page);

    /**
     * @abstract
     * @param \Sonata\PageBundle\Model\SnapshotInterface $snapshot
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    function load(SnapshotInterface $snapshot);

    /**
     * @abstract
     * @param \Sonata\PageBundle\Model\PageInterface $page
     * @return array
     */
    function getChildren(PageInterface $page);
}