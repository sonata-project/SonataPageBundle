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

use Symfony\Component\Process\Process;

/**
 * Create snapshots for a site
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class CreateSnapshotsCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:page:create-snapshots');
        $this->setDescription('Create a snapshots of all pages available');
        $this->addOption('site', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Site id', null);
        $this->addOption('base-console', null, InputOption::VALUE_OPTIONAL, 'Base symfony console command', 'php app/console');

        $this->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'Run the command asynchronously', 'sync');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
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

        foreach ($this->getSites($input) as $site) {
            if ($input->getOption('site') != 'all') {

                if ($input->getOption('mode') == 'async') {
                    $output->write(sprintf("<info>%s</info> - Publish a notification command ...", $site->getName()));
                } else {
                    $output->write(sprintf("<info>%s</info> - Generating snapshots ...", $site->getName()));
                }

                $this->getNotificationBackend($input->getOption('mode'))->createAndPublish('sonata.page.create_snapshots', array(
                    'siteId' => $site->getId(),
                    'mode'   => $input->getOption('mode')
                ));

                $output->writeln(" done!");
            } else {
                $p = new Process(sprintf('%s sonata:page:create-snapshots --env=%s --site=%s --mode=%s %s ', $input->getOption('base-console'), $input->getOption('env'), $site->getId(), $input->getOption('mode'), $input->getOption('no-debug') ? '--no-debug' : ''));
                $p->setTimeout(0);
                $p->run(function($type, $data) use ($output) {
                    $output->write($data, OutputInterface::OUTPUT_RAW);
                });
            }
        }

        $output->writeln("<info>done!</info>");
    }
}
