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
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Service\Contract\CleanupSnapshotBySiteInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'sonata:page:cleanup-snapshots', description: 'Cleanups the deprecated snapshots by a given site')]
final class CleanupSnapshotsCommand extends Command
{
    // TODO: Remove static properties when support for Symfony < 5.4 is dropped.
    protected static $defaultName = 'sonata:page:cleanup-snapshots';
    protected static $defaultDescription = 'Cleanups the deprecated snapshots by a given site';

    private CleanupSnapshotBySiteInterface $cleanupSnapshotBySite;

    private SiteManagerInterface $siteManager;

    public function __construct(
        CleanupSnapshotBySiteInterface $cleanupSnapshotBySite,
        SiteManagerInterface $siteManager
    ) {
        parent::__construct();

        $this->cleanupSnapshotBySite = $cleanupSnapshotBySite;
        $this->siteManager = $siteManager;
    }

    public function configure(): void
    {
        \assert(null !== static::$defaultDescription);

        $this
            // TODO: Remove setDescription when support for Symfony < 5.4 is dropped.
            ->setDescription(static::$defaultDescription)
            ->addOption('site', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Site id')
            ->addOption('keep-snapshots', null, InputOption::VALUE_REQUIRED, 'Keep a given count of snapshots per page', 5);
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
            $output->write(sprintf('<info>%s</info> - Cleaning up snapshots ...', $site->getName() ?? ''));

            $this->cleanupSnapshotBySite->cleanupBySite($site, (int) $keepSnapshots);

            $output->writeln(' done!');
        }

        $output->writeln('<info>done!</info>');

        return 0;
    }

    /**
     * @param array<string> $ids
     *
     * @return array<SiteInterface>
     */
    private function getSites(array $ids): array
    {
        if ([] === $ids) {
            return $this->siteManager->findAll();
        }

        return $this->siteManager->findBy(['id' => $ids]);
    }
}
