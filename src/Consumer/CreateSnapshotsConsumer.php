<?php

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
     * @deprecated This property is deprecated since version 2.4 and will be removed in 3.0
     */
    protected $pageInterface;

    /**
     * @param BackendInterface     $asyncBackend
     * @param BackendInterface     $runtimeBackend
     * @param PageManagerInterface $pageManager
     */
    public function __construct(BackendInterface $asyncBackend, BackendInterface $runtimeBackend, PageManagerInterface $pageManager)
    {
        $this->asyncBackend = $asyncBackend;
        $this->runtimeBackend = $runtimeBackend;
        $this->pageManager = $pageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $pages = $this->pageManager->findBy([
            'site' => $event->getMessage()->getValue('siteId'),
        ]);

        $backend = 'async' == $event->getMessage()->getValue('mode') ? $this->asyncBackend : $this->runtimeBackend;

        foreach ($pages as $page) {
            $backend->createAndPublish('sonata.page.create_snapshot', [
                'pageId' => $page->getId(),
            ]);
        }
    }
}
