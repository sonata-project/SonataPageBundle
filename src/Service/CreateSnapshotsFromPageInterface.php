<?php

namespace Sonata\PageBundle\Service;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SnapshotInterface;

interface CreateSnapshotsFromPageInterface
{
    /**
     * @param iterable<PageInterface> $pages
     * @return iterable<SnapshotInterface>
     */
    public function createFromPages(iterable $pages): iterable;
}