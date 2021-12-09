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
 * Consumer class to generate snapshots.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.x
 */
class CreateSnapshotsConsumer implements ConsumerInterface
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
     * @deprecated This property is deprecated since sonata-project/page-bundle 2.4 and will be removed in 4.0
     */
    protected $pageInterface;

    public function __construct(BackendInterface $asyncBackend, BackendInterface $runtimeBackend, PageManagerInterface $pageManager)
    {
        $this->asyncBackend = $asyncBackend;
        $this->runtimeBackend = $runtimeBackend;
        $this->pageManager = $pageManager;
    }

    public function process(ConsumerEvent $event)
    {
        $pages = $this->pageManager->findBy([
            'site' => $event->getMessage()->getValue('siteId'),
        ]);

        $backend = 'async' === $event->getMessage()->getValue('mode') ? $this->asyncBackend : $this->runtimeBackend;

        foreach ($pages as $page) {
            $backend->createAndPublish('sonata.page.create_snapshot', [
                'pageId' => $page->getId(),
            ]);
        }
    }
}
