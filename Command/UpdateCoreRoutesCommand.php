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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Sonata\PageBundle\Model\SiteInterface;

use Symfony\Component\Process\Process;

/**
 * Update core routes by reading routing information
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class UpdateCoreRoutesCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:page:update-core-routes');
        $this->setDescription('Update core routes, from routing files to page manager');
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Create snapshots for all sites');
        $this->addOption('site', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Site id', null);
        $this->addOption('base-command', null, InputOption::VALUE_OPTIONAL, 'Site id', 'php app/console');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('site') && !$input->getOption('all')) {
            $output->writeln('Please provide an <info>--site=SITE_ID</info> option or the <info>--site=all</info> directive');
            $output->writeln('');

            $output->writeln(sprintf(" % 5s - % -30s - %s", "ID", "Name", "Url"));

            foreach ($this->getSiteManager()->findBy(array()) as $site) {
                $output->writeln(sprintf(" % 5s - % -30s - %s", $site->getId(), $site->getName(), $site->getUrl()));
            }

            return;
        }

        foreach ($this->getSites($input) as $site) {
            if ($input->getOption('site') != 'all') {
                $this->getRoutePageGenerator()->update($site, $output);
                $output->writeln("");
            } else {
                $p = new Process(sprintf('%s sonata:page:update-core-routes --env=%s --site=%s %s', $input->getOption('base-command'), $input->getOption('env'), $site->getId(), $input->getOption('no-debug') ? '--no-debug' : ''));

                $p->run(function($type, $data) use ($output) {
                    $output->write($data);
                });
            }
        }

        $output->writeln("<info>done!</info>");
    }

    /**
     * Returns Sonata route page generator service
     *
     * @return \Sonata\PageBundle\Route\RoutePageGenerator
     */
    private function getRoutePageGenerator()
    {
        return $this->getContainer()->get('sonata.page.route.page.generator');
    }
}
