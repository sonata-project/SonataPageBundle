<?php

namespace Sonata\PageBundle\Service\Contract;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SnapshotInterface;

interface CreateSnapshotByPageInterface
{
    public function createByPage(PageInterface $page): SnapshotInterface;
}