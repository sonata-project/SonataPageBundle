<?php

namespace Sonata\PageBundle\Service\Contract;

use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteManagerInterface;

interface GetSitesFromCommandInterface
{
    public const ALL = 'all';
    /**
     * @param array<int>|array<self::ALL> $ids
     * @return array<Site>
     */
    public function findSitesById(array $ids): array;
}