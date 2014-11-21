<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;

/**
 * BaseCommand
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * @return \Sonata\PageBundle\Model\SiteManagerInterface
     */
    public function getSiteManager()
    {
        return $this->getContainer()->get('sonata.page.manager.site');
    }

    /**
     * @return \Sonata\PageBundle\Model\PageManagerInterface
     */
    public function getPageManager()
    {
        return $this->getContainer()->get('sonata.page.manager.page');
    }

    /**
     * @return \Sonata\PageBundle\Model\BlockManagerInterface
     */
    public function getBlockManager()
    {
        return $this->getContainer()->get('sonata.page.manager.block');
    }

    /**
     * @return \Sonata\PageBundle\Model\SnapshotManagerInterface
     */
    public function getSnapshotManager()
    {
        return $this->getContainer()->get('sonata.page.manager.snapshot');
    }

    /**
     * @return \Sonata\PageBundle\CmsManager\CmsPageManager
     */
    public function getCmsPageManager()
    {
        return $this->getContainer()->get('sonata.page.cms.page');
    }

    /**
     * @return \Sonata\PageBundle\CmsManager\DecoratorStrategyInterface
     */
    public function getDecoratorStrategy()
    {
        return $this->getContainer()->get('sonata.page.decorator_strategy');
    }

    /**
     * @return \Sonata\PageBundle\Listener\ExceptionListener
     */
    public function getErrorListener()
    {
        return $this->getContainer()->get('sonata.page.kernel.exception_listener');
    }

    /**
     * @param string $mode
     *
     * @return \Sonata\NotificationBundle\Backend\BackendInterface
     */
    public function getNotificationBackend($mode)
    {
        if ($mode == 'async') {
            return $this->getContainer()->get('sonata.notification.backend');
        }

        return $this->getContainer()->get('sonata.notification.backend.runtime');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return array
     */
    protected function getSites(InputInterface $input)
    {
        $parameters = array();
        $identifiers = $input->getOption('site');

        if ('all' != current($identifiers)) {
            $parameters['id'] = 1 === count($identifiers) ? current($identifiers) : $identifiers;
        }

        return $this->getSiteManager()->findBy($parameters);
    }
}
