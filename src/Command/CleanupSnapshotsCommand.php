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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Cleanups the deprecated snapshots.
 *
 * @final since sonata-project/page-bundle 3.26
 */
class CleanupSnapshotsCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('sonata:page:cleanup-snapshots');
        $this->setDescription('Cleanups the deprecated snapshots by a given site');

        $this->addOption('site', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Site id');
        //NEXT_MAJOR: Remove the "base-console" option.
        $this->addOption('base-console', null, InputOption::VALUE_OPTIONAL, 'Base Symfony console command', 'app/console');
        //NEXT_MAJOR: Remove the "mode" option.
        $this->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'Run the command asynchronously', 'sync');
        $this->addOption('keep-snapshots', null, InputOption::VALUE_OPTIONAL, 'Keep a given count of snapshots per page', 5);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        //NEXT_MAJOR: Remove this condition.
        if ('app/console' !== $input->getOption('base-console')) {
            @trigger_error(
                'The "base-console" is deprecated since sonata-project/page-bundle 3.27.0 and will be removed in 4.0',
                \E_USER_DEPRECATED
            );
        }

        if (!$input->getOption('site')) {
            $output->writeln('Please provide an <info>--site=SITE_ID</info> option or the <info>--site=all</info> directive');
            $output->writeln('');

            $output->writeln(sprintf(' % 5s - % -30s - %s', 'ID', 'Name', 'Url'));

            foreach ($this->getSiteManager()->findBy([]) as $site) {
                $output->writeln(sprintf(' % 5s - % -30s - %s', $site->getId(), $site->getName(), $site->getUrl()));
            }

            return;
        }

        //NEXT_MAJOR: Remove this condition.
        if (!\in_array($input->getOption('mode'), ['async', 'sync'], true)) {
            throw new \InvalidArgumentException('Option "mode" is not valid (async|sync).');
        }

        if (!is_numeric($input->getOption('keep-snapshots'))) {
            throw new \InvalidArgumentException('Please provide an integer value for the option "keep-snapshots".');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $siteOption = $input->getOption('site');
        $keepSnapshots = $input->getOption('keep-snapshots');

        //NEXT_MAJOR: Inject GetSitesFromCommand $getSites
        $getSites = $this->getContainer()->get('sonata.page.service.get_sites');

        foreach ($getSites->findSitesById($siteOption) as $site) {
            if ('async' === $input->getOption('mode')) {
                @trigger_error(
                    'The async mode is deprecated since sonata-project/page-bundle 3.27.0 and will be removed in 4.0',
                    \E_USER_DEPRECATED
                );
                $output->write(sprintf('<info>%s</info> - Publish a notification command ...', $site->getName()));

                $this->getNotificationBackend($input->getOption('mode'))->createAndPublish('sonata.page.cleanup_snapshots', [
                    'siteId' => $site->getId(),
                    'mode' => $input->getOption('mode'),
                    'keepSnapshots' => $keepSnapshots,
                ]);

                $output->writeln(' done!');
                continue;
            }

            $output->write(sprintf('<info>%s</info> - Cleaning up snapshots ...', $site->getName()));

            //NEXT_MAJOR: inject this class in the constructor CleanupSnapshotBySiteInterface $cleanupSnapshot
            $cleanupSnapshot = $this->getContainer()->get('sonata.page.service.cleanup_snapshot');
            $cleanupSnapshot->cleanupBySite($site, $keepSnapshots);

            $output->writeln(' done!');
        }

        $output->writeln('<info>done!</info>');

        return 0;
    }
}
