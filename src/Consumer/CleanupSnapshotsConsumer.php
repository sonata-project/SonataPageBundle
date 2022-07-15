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

namespace Sonata\PageBundle\Consumer;

use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Sonata\PageBundle\Model\PageManagerInterface;

/**
 * Consumer class to cleanup snapshots.
 *
 * NEXT_MAJOR: Remove this class
 *
 * @final since sonata-project/page-bundle 3.26
 *
 * @deprecated since 3.27, and it will be removed in 4.0.
 */
class CleanupSnapshotsConsumer implements ConsumerInterface
{
    /**
     * @var BackendInterface
     */
    protected $asyncBackend;

    /**
     * @var BackendInterface
     */
    protected $runtimeBackend;

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @param BackendInterface     $asyncBackend   An asynchronous backend instance
     * @param BackendInterface     $runtimeBackend A runtime backend instance
     * @param PageManagerInterface $pageManager    A page manager instance
     */
    public function __construct(BackendInterface $asyncBackend, BackendInterface $runtimeBackend, PageManagerInterface $pageManager)
    {
        @trigger_error(
            sprintf(
                'This %s is deprecated since sonata-project/page-bundle 3.27.0'.
                ' and will be removed in 4.0',
                self::class
            ),
            \E_USER_DEPRECATED
        );

        $this->asyncBackend = $asyncBackend;
        $this->runtimeBackend = $runtimeBackend;
        $this->pageManager = $pageManager;
    }

    public function process(ConsumerEvent $event): void
    {
        $pages = $this->pageManager->findBy([
            'site' => $event->getMessage()->getValue('siteId'),
        ]);

        $backend = 'async' === $event->getMessage()->getValue('mode') ? $this->asyncBackend : $this->runtimeBackend;
        $keepSnapshots = $event->getMessage()->getValue('keepSnapshots');

        foreach ($pages as $page) {
            $backend->createAndPublish('sonata.page.cleanup_snapshot', [
                'pageId' => $page->getId(),
                'keepSnapshots' => $keepSnapshots,
            ]);
        }
    }
}
