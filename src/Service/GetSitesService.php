<?php

namespace Sonata\PageBundle\Service;

use Sonata\PageBundle\Exception\ParameterNotAllowedException;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Service\Contract\GetSitesFromCommandInterface;

final class GetSitesService implements GetSitesFromCommandInterface
{
    private const ALL = 'all';

    private $siteManager;

    public function __construct(SiteManagerInterface $siteManager)
    {
        $this->siteManager = $siteManager;
    }

    /**
     * @param array<int>|array<self::ALL> $ids
     * @return array<Site>
     * @throws ParameterNotAllowedException
     */
    public function findSitesById(array $ids): array
    {
        $hasInvalidString = array_filter($ids, function ($id) {
            return is_string($id) && $id !== self::ALL;
        });

        if ($hasInvalidString) {
            throw new ParameterNotAllowedException;
        }

        if ([self::ALL] === $ids) {
            return $this->siteManager->findAll();
        }

        return $this->siteManager->findBy(['id' => $ids]);
    }
}