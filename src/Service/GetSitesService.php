<?php

namespace Sonata\PageBundle\Service;

use Sonata\PageBundle\Exception\ParameterNotAllowedException;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Service\Contract\GetSitesFromCommand;

final class GetSitesService implements GetSitesFromCommand
{
    private $siteManager;

    public function __construct(SiteManagerInterface $siteManager)
    {
        $this->siteManager = $siteManager;
    }

    /**
     * @param array<int>|array<SiteManagerInterface::ALL> $ids
     * @return array<Site>
     * @throws ParameterNotAllowedException
     */
    public function findSitesById(array $ids): array
    {
        $hasInvalidString = array_filter($ids, function ($id) {
            return is_string($id) && $id !== SiteManagerInterface::ALL;
        });

        if ($hasInvalidString) {
            throw new ParameterNotAllowedException(sprintf(
                'The parameter "%s" is not allowed.'
                , $hasInvalidString[0])
            );
        }

        if ([SiteManagerInterface::ALL] === $ids) {
            return $this->siteManager->findAll();
        }

        return $this->siteManager->findBy(['id' => $ids]);
    }
}