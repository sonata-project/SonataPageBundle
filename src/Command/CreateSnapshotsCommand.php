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
use Sonata\PageBundle\Service\Contract\CreateSnapshotBySiteInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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

    /**
     * @var ?string|CreateSnapshotBySiteInterface
     *
     * NEXT_MAJOR restrict to CreateSnapshotBySiteInterface
     */
    private $createSnapshot;

    /**
     * NEXT_MAJOR: Remove the default value for $createSnapshot and add the CreateSnapshotFromSiteInterface type.
     */
    public function __construct($createSnapshot = null)
    {
        //NEXT_MAJOR: Remove the "if" condition and let only the "else" code.
        if (\is_string($createSnapshot) || null === $createSnapshot) {
            @trigger_error(sprintf(
                'The %s class is final since sonata-project/page-bundle 3.27.0 and and it will be removed in 4.0'
                 .'release you won\'t be able to extend this class anymore.',
                self::class
            ), \E_USER_DEPRECATED);
            parent::__construct($createSnapshot);
        } else {
            parent::__construct(self::$defaultName);
            $this->createSnapshot = $createSnapshot;
        }
    }

    public function configure()
    {
        $this->setDescription('Create a snapshots of all pages available');
        $this->addOption('site', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Site id');
        //NEXT_MAJOR: Remove the "base-console" option.
        $this->addOption('base-console', null, InputOption::VALUE_OPTIONAL, 'Base symfony console command', 'php app/console');
        //NEXT_MAJOR: Remove the "mode" option.
        $this->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'Run the command asynchronously', 'sync');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $siteOption = $input->getOption('site');

        //NEXT_MAJOR: Remove this condition.
        if ('php app/console' !== $input->getOption('base-console')) {
            @trigger_error(
                'The "base-console" is deprecated since sonata-project/page-bundle 3.27.0 and will be removed in 4.0',
                \E_USER_DEPRECATED
            );
        }

        //NEXT_MAJOR: Remove this condition, because site will be optional
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
            // NEXT_MAJOR: Remove this "async" condition block.
            if ('async' === $input->getOption('mode')) {
                @trigger_error(
                    'The async mode is deprecated since sonata-project/page-bundle 3.27.0 and will be removed in 4.0',
                    \E_USER_DEPRECATED
                );

                $output->write(sprintf('<info>%s</info> - Publish a notification command ...', $site->getName()));

                $this->getNotificationBackend($input->getOption('mode'))->createAndPublish('sonata.page.create_snapshots', [
                    'siteId' => $site->getId(),
                    'mode' => $input->getOption('mode'),
                ]);

                $output->writeln(' done!');
                continue;
            }

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
