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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Create snapshots for a site.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class CreateSnapshotsCommand extends BaseCommand
{
    protected static $defaultName = 'sonata:page:create-snapshots';

    public function configure()
    {
        $this->setDescription('Create a snapshots of all pages available');
        $this->addOption('site', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Site id', ['all']);
        $this->addOption('base-console', null, InputOption::VALUE_OPTIONAL, 'Base symfony console command', 'php app/console');
        $this->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'Run the command asynchronously', 'sync');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $sites = $this->getSites($input);

        foreach ($sites as $site) {
            // NEXT_MAJOR: Remove this "async" condition block.
            if ('async' === $input->getOption('mode')) {
                @trigger_error(sprintf(
                    'The async mode is deprecated since sonata-project/page-bundle 3.27.0 and will be removed in 4.0'
                ), \E_USER_DEPRECATED);

                $output->write(sprintf('<info>%s</info> - Publish a notification command ...', $site->getName()));

                $this->getNotificationBackend($input->getOption('mode'))->createAndPublish('sonata.page.create_snapshots', [
                    'siteId' => $site->getId(),
                    'mode' => $input->getOption('mode'),
                ]);

                $output->writeln(' done!');
                continue;
            }

            $output->write(sprintf('<info>%s</info> - Generating snapshots ...', $site->getName()));
            //TODO need to call CreateSnapshotsService!!!!

            $output->writeln(' done!');
        }

        $output->writeln('<info>done!</info>');

        return 0;
    }
}
