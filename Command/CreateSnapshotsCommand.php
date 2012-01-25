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
use Sonata\PageBundle\Model\SiteInterface;

use Symfony\Component\Process\Process;

class CreateSnapshotsCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('sonata:page:create-snapshots');
        $this->setDescription('Create a snapshots of all pages available');
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Create snapshots for all sites');
        $this->addOption('site', null, InputOption::VALUE_OPTIONAL, 'Site id', null);
        $this->addOption('base-command', null, InputOption::VALUE_OPTIONAL, 'Site id', 'php app/console');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('site') && !$input->getOption('all')) {
            $output->writeln('Please provide an <info>--site=SITE_ID</info> option or the <info>--all</info> directive');
            $output->writeln('');

            $output->writeln(sprintf(" % 5s - % -30s - %s", "ID", "Name", "Url"));

            foreach ($this->getSiteManager()->findBy() as $site) {
                $output->writeln(sprintf(" % 5s - % -30s - %s", $site->getId(), $site->getName(), $site->getUrl()));
            }

            return;
        }

        foreach ($this->getSites($input) as $site) {
            if ($input->getOption('site')) {
                $this->createSnapshot($site, $output);
                $output->writeln("");
            } else {

                $p = new Process(sprintf('%s sonata:page:create-snapshots --env=%s --site=%s %s', $input->getOption('base-command'), $input->getOption('env'), $site->getId(), $input->getOption('no-debug') ? '--no-debug' : ''));

                $p->run(function($type, $data) use($output) {
                    $output->write($data);
                });
            }
        }

        $output->writeln("<info>done!</info>");
    }

    /**
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    private function createSnapshot(SiteInterface $site, OutputInterface $output)
    {
        $message = sprintf(" > <info>Create snapshots for site</info> : <comment>%s - %s</comment>", $site->getName(), $site->getUrl());

        $output->writeln(array(
            str_repeat('=', strlen($message)),
            "",
            $message,
            "",
            str_repeat('=', strlen($message)),
        ));

        $this->getSnapshotManager()->getConnection()->beginTransaction();

        $snapshots = array();

        $pages = $this->getPageManager()->findBy(array('site' => $site->getId()));

        $count = count($pages);
        foreach ($pages as $pos => $page) {
            $output->write(sprintf('  <info>%03d/%03d</info> % -50s ...', $pos + 1, $count, $page->getUrl()));

            $snapshot = $this->getSnapshotManager()->create($page);

            $this->getSnapshotManager()->save($snapshot);

            $output->writeln(' OK !');
            $snapshots[] = $snapshot;
        }

        $output->writeln('');
        $output->write('  Enabling snapshots ...');

        $this->getSnapshotManager()->enableSnapshots($site, $snapshots);
        $this->getSnapshotManager()->getConnection()->commit();

        $output->writeln(' <comment>OK</comment> !');
    }
}