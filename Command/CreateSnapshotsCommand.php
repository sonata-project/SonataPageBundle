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

class CreateSnapshotsCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:page:create-snapshots');
        $this->setDescription('Create a snapshots of all pages available');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $pageManager = $this->getContainer()->get('sonata.page.manager.page');
        $snapshotManager = $this->getContainer()->get('sonata.page.manager.snapshot');

        $snapshotManager->getConnection()->beginTransaction();

        $snapshots = array();
        $pages = $pageManager->findBy();
        $count = count($pages);
        foreach ($pages as $pos => $page) {
            $output->write(sprintf('<info>%03d/%03d</info> % -50s ...', $pos + 1, $count, $page->getUrl()));
            $snapshot = $snapshotManager->create($page);
            $snapshotManager->save($snapshot);

            $output->writeln(' OK !');
            $snapshots[] = $snapshot;
        }

        $output->writeln('');
        $output->write('Enabling snapshots ...');

        $snapshotManager->enableSnapshots($snapshots);

        $snapshotManager->getConnection()->commit();

        $output->writeln(' OK !');
    }
}