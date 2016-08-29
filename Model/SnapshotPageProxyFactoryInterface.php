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

interface SnapshotPageProxyFactoryInterface
{
    /**
     * Create snapshot instance.
     *
     * @param SnapshotManagerInterface $manager
     * @param TransformerInterface     $transformer
     * @param SnapshotInterface        $snapshot
     *
     * @return SnapshotPageProxyInterface
     */
    public function create(SnapshotManagerInterface $manager, TransformerInterface $transformer, SnapshotInterface $snapshot);
}
