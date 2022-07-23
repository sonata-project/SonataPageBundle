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

use Sonata\BlockBundle\Model\BlockManagerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\Site;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * BaseCommand.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * NEXT_MAJOR: Remove this class, and for all commands that use this class need to extend from Symfony command.
 *
 * @deprecated since 3.27, and it will be removed in 4.0.
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
     *
     * NEXT_MAJOR: Remove this method
     *
     * @deprecated since 3.27, and it will be removed in 4.0.
     */
    public function getNotificationBackend($mode)
    {
        if ('async' === $mode) {
            return $this->getContainer()->get('sonata.notification.backend');
        }

        return $this->getContainer()->get('sonata.notification.backend.runtime');
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        if (false !== strpos($this->getName(), 'sonata')) {
            @trigger_error(
                sprintf(
                    'The %s class is deprecate since sonata-project/page-bundle 3.27.0 and it will be remove in 4.0',
                    self::class
                ),
                \E_USER_DEPRECATED
            );
        }

        return parent::run($input, $output);
    }

    /**
     * @return Site[]
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
