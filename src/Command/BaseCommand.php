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

use Psr\Container\ContainerInterface;
use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
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
    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var BackendInterface
     */
    public $notificationBackendRuntime;

    /**
     * @var SiteManagerInterface
     */
    protected $siteManager;

    /**
     * @var BackendInterface
     */
    protected $notificationBackend;

    public function __construct(
        ?string $name = null,
        ContainerInterface $container,
        ?BackendInterface $backend = null,
        ?BackendInterface $backendRuntime = null,
        ?SiteManagerInterface $siteManager = null
    ) {
        parent::__construct($name);

        $this->container = $container;
        $this->notificationBackend = $backend ?? $container->get('sonata.notification.backend');
        $this->notificationBackendRuntime = $backendRuntime ?? $container->get('sonata.notification.backend.runtime');
        $this->siteManager = $siteManager;
    }

    /**
     * @deprecated since sonata-project/page-bundle 3.x, to be removed in version 4.0.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @deprecated since sonata-project/page-bundle 3.x, to be removed in version 4.0.
     *
     * @return SiteManagerInterface
     */
    public function getSiteManager()
    {
        return $this->siteManager ?? $this->container->get('sonata.page.manager.site');
    }

    /**
     * @deprecated since sonata-project/page-bundle 3.x, to be removed in version 4.0.
     *
     * @return PageManagerInterface
     */
    public function getPageManager()
    {
        return $this->container->get('sonata.page.manager.page');
    }

    /**
     * @deprecated since sonata-project/page-bundle 3.x, to be removed in version 4.0.
     *
     * @return BlockManagerInterface
     */
    public function getBlockManager()
    {
        return $this->container->get('sonata.page.manager.block');
    }

    /**
     * @deprecated since sonata-project/page-bundle 3.x, to be removed in version 4.0.
     *
     * @return SnapshotManagerInterface
     */
    public function getSnapshotManager()
    {
        return $this->container->get('sonata.page.manager.snapshot');
    }

    /**
     * @deprecated since sonata-project/page-bundle 3.x, to be removed in version 4.0.
     *
     * @return CmsPageManager
     */
    public function getCmsPageManager()
    {
        return $this->container->get('sonata.page.cms.page');
    }

    /**
     * @deprecated since sonata-project/page-bundle 3.x, to be removed in version 4.0.
     *
     * @return DecoratorStrategyInterface
     */
    public function getDecoratorStrategy()
    {
        return $this->container->get('sonata.page.decorator_strategy');
    }

    /**
     * @deprecated since sonata-project/page-bundle 3.x, to be removed in version 4.0.
     *
     * @return ExceptionListener
     */
    public function getErrorListener()
    {
        return $this->container->get('sonata.page.kernel.exception_listener');
    }

    /**
     * @param string $mode
     *
     * @return BackendInterface
     */
    public function getNotificationBackend($mode)
    {
        if ('async' === $mode) {
            return $this->notificationBackend;
        }

        return $this->notificationBackendRuntime;
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

        return $this->getSiteManager()->findBy($parameters);
    }
}
