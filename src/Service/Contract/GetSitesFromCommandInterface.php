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

namespace Sonata\PageBundle\Service\Contract;

use Sonata\PageBundle\Model\Site;

interface GetSitesFromCommandInterface
{
    public const ALL = 'all';

    /**
     * @param array<int>|array<self::ALL> $ids
     *
     * @return array<Site>
     */
    public function findSitesById(array $ids): array;
}
