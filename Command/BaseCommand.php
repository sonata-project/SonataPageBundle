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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

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
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return array
     */
    protected function getSites(InputInterface $input)
    {
        $parameters = array();
        if ($input->getOption('site') != 'all') {
            $parameters['id'] = $input->getOption('site');
        }

        return $this->getSiteManager()->findBy($parameters);
    }
}