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
 *
 * @final since sonata-project/page-bundle 3.26
 */
class CleanupSnapshotsCommand extends BaseCommand
{
    protected static $defaultName = 'sonata:page:cleanup-snapshots';

    public function configure()
    {
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

        //NEXT_MAJOR: Remove this condition.
        if (!\in_array($input->getOption('mode'), ['async', 'sync'], true)) {
            throw new \InvalidArgumentException('Option "mode" is not valid (async|sync).');
        }

        if (!is_numeric($input->getOption('keep-snapshots'))) {
            throw new \InvalidArgumentException('Please provide an integer value for the option "keep-snapshots".');
        }

        return 0;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $siteOption = $input->getOption('site');
        $keepSnapshots = $input->getOption('keep-snapshots');

        //NEXT_MAJOR: Remove this condition, because site will be optional.
        if ([] === $siteOption) {
            $output->writeln('Please provide an <info>--site=SITE_ID</info> option or the <info>--site=all</info> directive');

            return 1;
        }

        //NEXT_MAJOR: Remove this block condition.
        if (['all'] === $siteOption) {
            @trigger_error(
                sprintf(
                    '--site=all option is deprecate since sonata-project/page-bundle 3.27.0 and will be removed in 4.0'.
                'you just need to run: bin/console %s',
                    self::$defaultName
                ),
                \E_USER_DEPRECATED
            );

            $siteOption = [];
        }

        foreach ($this->getSites($siteOption) as $site) {
            //NEXT_MAJOR: Remove this "async" block condition.
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
