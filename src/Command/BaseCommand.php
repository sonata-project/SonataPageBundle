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
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * BaseCommand.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseCommand extends Command implements ServiceSubscriberInterface
{
    /** @var ContainerInterface */
    protected $locator;

    public function __construct(ContainerInterface $locator)
    {
        parent::__construct();

        $this->locator = $locator;
    }

    public static function getSubscribedServices(): array
    {
        return [
            'sonata.page.manager.site' => SiteManagerInterface::class,
            'sonata.page.manager.page' => PageManagerInterface::class,
            'sonata.page.manager.snapshot' => SnapshotManagerInterface::class,
            'sonata.page.manager.block' => ManagerInterface::class,
            'sonata.page.cms.page' => CmsManagerInterface::class,
            'sonata.page.kernel.exception_listener' => ExceptionListener::class,
            'sonata.notification.backend' => BackendInterface::class,
            'sonata.notification.backend.runtime' => BackendInterface::class,
        ];
    }

    /**
     * @param string $mode
     *
     * @return BackendInterface
     */
    public function getNotificationBackend($mode)
    {
        if ('async' === $mode) {
            return ($this->locator)('sonata.notification.backend');
        }

        return ($this->locator)('sonata.notification.backend.runtime');
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

        return ($this->locator)('sonata.page.manager.site')->findBy($parameters);
    }
}
