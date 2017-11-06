<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Command;

use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;

/**
 * BaseCommand.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @return SiteManagerInterface
     */
    public function getSiteManager()
    {
        return $this->getContainer()->get('sonata.page.manager.site');
    }

    /**
     * @return PageManagerInterface
     */
    public function getPageManager()
    {
        return $this->getContainer()->get('sonata.page.manager.page');
    }

    /**
     * @return BlockManagerInterface
     */
    public function getBlockManager()
    {
        return $this->getContainer()->get('sonata.page.manager.block');
    }

    /**
     * @return SnapshotManagerInterface
     */
    public function getSnapshotManager()
    {
        return $this->getContainer()->get('sonata.page.manager.snapshot');
    }

    /**
     * @return CmsPageManager
     */
    public function getCmsPageManager()
    {
        return $this->getContainer()->get('sonata.page.cms.page');
    }

    /**
     * @return DecoratorStrategyInterface
     */
    public function getDecoratorStrategy()
    {
        return $this->getContainer()->get('sonata.page.decorator_strategy');
    }

    /**
     * @return ExceptionListener
     */
    public function getErrorListener()
    {
        return $this->getContainer()->get('sonata.page.kernel.exception_listener');
    }

    /**
     * @param string $mode
     *
     * @return BackendInterface
     */
    public function getNotificationBackend($mode)
    {
        if ('async' == $mode) {
            return $this->getContainer()->get('sonata.notification.backend');
        }

        return $this->getContainer()->get('sonata.notification.backend.runtime');
    }

    /**
     * @param InputInterface $input
     *
     * @return array
     */
    protected function getSites(InputInterface $input)
    {
        $parameters = [];
        $identifiers = $input->getOption('site');

        if ('all' != current($identifiers)) {
            $parameters['id'] = 1 === count($identifiers) ? current($identifiers) : $identifiers;
        }

        return $this->getSiteManager()->findBy($parameters);
    }
}
