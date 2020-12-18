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

use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;

final class NotificationPublisher implements Publisher
{
    /**
     * @var BackendInterface
     */
    private $backend;

    public function __construct(BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    public function removeSnapshots(SiteInterface $site, int $keep = 0): void
    {
        $this->backend->createAndPublish('sonata.page.cleanup_snapshot', [
            'siteId' => $site->getId(),
            'keepSnapshots' => $keep,
        ]);
    }

    public function removeSnapshot(PageInterface $page, int $keep = 0): void
    {
        $this->backend->createAndPublish('sonata.page.cleanup_snapshot', [
            'pageId' => $page->getId(),
            'keepSnapshots' => $keep,
        ]);
    }

    public function createSnapshots(SiteInterface $site): void
    {
        $this->backend->createAndPublish('sonata.page.create_snapshots', [
            'siteId' => $site->getId(),
        ]);
    }

    public function createSnapshot(PageInterface $page): void
    {
        $this->backend->createAndPublish('sonata.page.create_snapshot', [
            'pageId' => $page->getId(),
        ]);
    }
}
