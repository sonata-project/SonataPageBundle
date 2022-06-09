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

namespace Sonata\PageBundle\Service;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;

interface CreateSnapshotsFromPageInterface
{
    /**
     * @param iterable<PageInterface> $pages
     *
     * @return iterable<SnapshotInterface>
     */
    public function createFromPages(iterable $pages): iterable;
}
