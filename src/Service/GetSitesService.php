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

use Sonata\PageBundle\Exception\ParameterNotAllowedException;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Service\Contract\GetSitesFromCommandInterface;

final class GetSitesService implements GetSitesFromCommandInterface
{
    private SiteManagerInterface $siteManager;

    public function __construct(SiteManagerInterface $siteManager)
    {
        $this->siteManager = $siteManager;
    }

    /**
     * @param array<int>|array<self::ALL> $ids
     *
     * @throws ParameterNotAllowedException
     *
     * @return array<Site>
     */
    public function findSitesById(array $ids): array
    {
        $hasInvalidString = array_filter($ids, static function ($id) {
            return \is_string($id) && self::ALL !== $id;
        });

        if ($hasInvalidString) {
            throw new ParameterNotAllowedException();
        }

        if ([self::ALL] === $ids) {
            return $this->siteManager->findAll();
        }

        return $this->siteManager->findBy(['id' => $ids]);
    }
}
