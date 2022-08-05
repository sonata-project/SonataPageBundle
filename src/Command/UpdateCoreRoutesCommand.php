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
use Sonata\PageBundle\Route\RoutePageGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update core routes by reading routing information.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
#[AsCommand(name: 'sonata:page:update-core-routes', description: 'Update core routes, from routing files to page manager')]
final class UpdateCoreRoutesCommand extends Command
{
    // TODO: Remove static properties when support for Symfony < 5.4 is dropped.
    protected static $defaultName = 'sonata:page:update-core-routes';
    protected static $defaultDescription = 'Update core routes, from routing files to page manager';

    private SiteManagerInterface $siteManager;
    private RoutePageGenerator $pageGenerator;

    public function __construct(
        SiteManagerInterface $siteManager,
        RoutePageGenerator $pageGenerator
    ) {
        parent::__construct();

        $this->siteManager = $siteManager;
        $this->pageGenerator = $pageGenerator;
    }

    public function configure(): void
    {
        \assert(null !== static::$defaultDescription);

        $this
            // TODO: Remove setDescription when support for Symfony < 5.4 is dropped.
            ->setDescription(static::$defaultDescription)
            ->addOption('clean', null, InputOption::VALUE_NONE, 'Removes all unused routes')
            ->addOption('site', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Site id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $siteOption = $input->getOption('site');

        foreach ($this->getSites($siteOption) as $site) {
            $this->pageGenerator->update($site, $output, $input->getOption('clean'));
            $output->writeln('');
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
