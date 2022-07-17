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

use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Cleanups the deprecated snapshots.
 */
final class CleanupSnapshotsCommand extends BaseCommand
{
    protected static $defaultName = 'sonata:page:cleanup-snapshots';

    public function configure(): void
    {
        $this->setDescription('Cleanups the deprecated snapshots by a given site');

        $this->addOption('site', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Site id');
        $this->addOption('keep-snapshots', null, InputOption::VALUE_OPTIONAL, 'Keep a given count of snapshots per page', 5);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if (!is_numeric($input->getOption('keep-snapshots'))) {
            throw new \InvalidArgumentException('Please provide an integer value for the option "keep-snapshots".');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $siteOption = $input->getOption('site');
        $keepSnapshots = $input->getOption('keep-snapshots');

        foreach ($this->getSites($siteOption) as $site) {
            $output->write(sprintf('<info>%s</info> - Cleaning up snapshots ...', $site->getName()));

            //NEXT_MAJOR: inject this class in the constructor CleanupSnapshotBySiteInterface $cleanupSnapshot
            $cleanupSnapshot = $this->getContainer()->get('sonata.page.service.cleanup_snapshot');
            $cleanupSnapshot->cleanupBySite($site, $keepSnapshots);

            $output->writeln(' done!');
        }

        $output->writeln('<info>done!</info>');

        return 0;
    }

    /**
     * @param array<int> $ids
     *
     * @return array<SiteInterface>
     *
     * NEXT_MAJOR: add array type for $ids
     */
    protected function getSites($ids): array
    {
        //NEXT_MAJOR: Inject this on the __construct.
        $siteManager = $this->getContainer()->get('sonata.page.manager.site');

        if ([] === $ids) {
            return $siteManager->findAll();
        }

        return $siteManager->findBy(['id' => $ids]);
    }
}
