<?php

namespace Sonata\PageBundle\Service\Contract;

use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteManagerInterface;

interface GetSitesFromCommand
{
    /**
     * @param array<int>|array<SiteManagerInterface::ALL> $ids
     * @return array<Site>
     */
    public function findSitesById(array $ids): array;
}