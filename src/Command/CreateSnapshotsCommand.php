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
use Sonata\PageBundle\Service\Contract\CreateSnapshotBySiteInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create snapshots for a site.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class CreateSnapshotsCommand extends Command
{
    protected static $defaultName = 'sonata:page:create-snapshots';

    private CreateSnapshotBySiteInterface $createSnapshot;
    private SiteManagerInterface $siteManager;

    public function __construct(CreateSnapshotBySiteInterface $createSnapshot, SiteManagerInterface $siteManager)
    {
        parent::__construct();

        $this->createSnapshot = $createSnapshot;
        $this->siteManager = $siteManager;
    }

    public function configure(): void
    {
        $this->setDescription('Create a snapshots of all pages available');
        $this->addOption('site', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Site id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $siteOption = $input->getOption('site');

        foreach ($this->getSites($siteOption) as $site) {
            $output->write(sprintf('<info>%s</info> - Generating snapshots ...', $site->getName()));

            $this->createSnapshot->createBySite($site);
            $output->writeln(' done!');
        }

        $output->writeln('<info>done!</info>');

        return 0;
    }

    /**
     * @param array<int> $ids
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
