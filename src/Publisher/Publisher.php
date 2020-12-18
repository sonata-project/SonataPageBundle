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

namespace Sonata\PageBundle\Publisher;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;

/**
 * @author Christian Gripp <mail@core23.de>
 */
interface Publisher
{
    public function createSnapshots(SiteInterface $site): void;

    public function createSnapshot(PageInterface $page): void;

    public function removeSnapshots(SiteInterface $site, int $keep = 0): void;

    public function removeSnapshot(PageInterface $page, int $keep = 0): void;
}
