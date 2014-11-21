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
use Symfony\Component\Process\Process;

/**
 * Cleanups the deprecated snapshots
 */
class CleanupSnapshotsCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:page:cleanup-snapshots');
        $this->setDescription('Cleanups the deprecated snapshots by a given site');

        $this->addOption('site', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Site id', null);
        $this->addOption('base-console', null, InputOption::VALUE_OPTIONAL, 'Base Symfony console command', 'app/console');
        $this->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'Run the command asynchronously', 'sync');
        $this->addOption('keep-snapshots', null, InputOption::VALUE_OPTIONAL, 'Keep a given count of snapshots per page', 5);
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('site')) {
            $output->writeln('Please provide an <info>--site=SITE_ID</info> option or the <info>--site=all</info> directive');
            $output->writeln('');

            $output->writeln(sprintf(" % 5s - % -30s - %s", "ID", "Name", "Url"));

            foreach ($this->getSiteManager()->findBy(array()) as $site) {
                $output->writeln(sprintf(" % 5s - % -30s - %s", $site->getId(), $site->getName(), $site->getUrl()));
            }

            return;
        }

        if (!in_array($input->getOption('mode'), array('async', 'sync'))) {
            throw new \InvalidArgumentException('Option "mode" is not valid (async|sync).');
        }

        if (!is_numeric($input->getOption('keep-snapshots'))) {
            throw new \InvalidArgumentException('Please provide an integer value for the option "keep-snapshots".');
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getSites($input) as $site) {
            if ($input->getOption('site') != 'all') {
                if ($input->getOption('mode') == 'async') {
                    $output->write(sprintf("<info>%s</info> - Publish a notification command ...", $site->getName()));
                } else {
                    $output->write(sprintf("<info>%s</info> - Cleanuping snapshots ...", $site->getName()));
                }

                $this->getNotificationBackend($input->getOption('mode'))->createAndPublish('sonata.page.cleanup_snapshots', array(
                    'siteId'        => $site->getId(),
                    'mode'          => $input->getOption('mode'),
                    'keepSnapshots' => $input->getOption('keep-snapshots'),
                ));

                $output->writeln(" done!");
            } else {
                $p = new Process(sprintf('%s sonata:page:cleanup-snapshots --env=%s --site=%s --mode=%s --keep-snapshots=%s %s',
                    $input->getOption('base-console'),
                    $input->getOption('env'),
                    $site->getId(),
                    $input->getOption('mode'),
                    $input->getOption('keep-snapshots'),
                    $input->getOption('no-debug') ? '--no-debug' : ''
                ));

                $p->run(function($type, $data) use ($output) {
                    $output->write($data, OutputInterface::OUTPUT_RAW);
                });
            }
        }

        $output->writeln("<info>done!</info>");
    }
}