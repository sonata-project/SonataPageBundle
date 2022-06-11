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

namespace Sonata\PageBundle\Command;

use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * BaseCommand.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseCommand extends Command
{
    /** @var SiteManagerInterface */
    protected $siteManager;

    /** @var PageManagerInterface */
    protected $pageManager;

    /** @var SnapshotManagerInterface */
    protected $snapshotManager;

    /** @var ManagerInterface */
    protected $blockManager;

    /** @var CmsManagerInterface */
    protected $cmsPageManager;

    /** @var ExceptionListener */
    protected $exceptionListener;

    /** @var BackendInterface */
    protected $backend;

    /** @var BackendInterface */
    protected $backendRuntime;

    public function __construct(
        SiteManagerInterface $siteManager,
        PageManagerInterface $pageManager,
        SnapshotManagerInterface $snapshotManager,
        ManagerInterface $blockManager,
        CmsManagerInterface $cmsPageManager,
        ExceptionListener $exceptionListener,
        BackendInterface $backend,
        BackendInterface $backendRuntime
    ) {
        parent::__construct();

        $this->siteManager = $siteManager;
        $this->pageManager = $pageManager;
        $this->snapshotManager = $snapshotManager;
        $this->blockManager = $blockManager;
        $this->cmsPageManager = $cmsPageManager;
        $this->exceptionListener = $exceptionListener;
        $this->backend = $backend;
        $this->backendRuntime = $backendRuntime;
    }

    /**
     * @param string $mode
     *
     * @return BackendInterface
     */
    public function getNotificationBackend($mode)
    {
        if ('async' === $mode) {
            return $this->backend;
        }

        return $this->backendRuntime;
    }

    /**
     * @return array
     */
    protected function getSites(InputInterface $input)
    {
        $parameters = [];
        $identifiers = $input->getOption('site');

        if ('all' !== current($identifiers)) {
            $parameters['id'] = 1 === \count($identifiers) ? current($identifiers) : $identifiers;
        }

        return $this->siteManager->findBy($parameters);
    }
}
