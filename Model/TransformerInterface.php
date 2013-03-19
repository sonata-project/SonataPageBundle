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

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
interface TransformerInterface
{
    /**
     * @param SnapshotInterface $snapshot
     *
     * @return PageInterface
     */
    public function load(SnapshotInterface $snapshot);

    /**
     * @param PageInterface $page
     *
     * @return SnapshotInterface
     */
    public function create(PageInterface $page);

    /**
     * @param PageInterface $page
     *
     * @return array
     */
    public function getChildren(PageInterface $page);

    /**
     * @param array         $content
     * @param PageInterface $page
     *
     * @return BlockInterface
     */
    public function loadBlock(array $content, PageInterface $page);
}