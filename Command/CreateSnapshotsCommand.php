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

class CreateSnapshotsCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('sonata:page:create-snapshots');
        $this->setDescription('Create a snapshots of all pages available');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getSiteManager()->findBy() as $site) {
            $this->createSnapshot($site, $output);
            $output->writeln("");
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