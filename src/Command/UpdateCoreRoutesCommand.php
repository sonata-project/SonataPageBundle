<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Command;

use Sonata\PageBundle\Route\RoutePageGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Update core routes by reading routing information.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class UpdateCoreRoutesCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:page:update-core-routes');
        $this->setDescription('Update core routes, from routing files to page manager');
        // NEXT_MAJOR: Remove the "all" option.
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Create snapshots for all sites');
        $this->addOption('clean', null, InputOption::VALUE_NONE, 'Removes all unused routes');
        $this->addOption('site', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Site id', null);
        $this->addOption('base-command', null, InputOption::VALUE_OPTIONAL, 'Site id', 'php app/console');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            @trigger_error(
                'Using the "all" option is deprecated since 3.4 and will be removed in 4.0.',
                E_USER_DEPRECATED
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

        foreach ($this->getSites($input) as $site) {
            if ('all' != $input->getOption('site')) {
                $this->getRoutePageGenerator()->update($site, $output, $input->getOption('clean'));
                $output->writeln('');
            } else {
                $builder = ProcessBuilder::create($input->getOption('base-command'))
                    ->add('sonata:page:update-core-routes')
                    ->setOption('env', $input->getOption('env'))
                    ->setOption('site', $site->getId());

                if ($input->getOption('no-debug')) {
                    $builder->add('--no-debug');
                }

                if ($input->getOption('clean')) {
                    $builder->add('--clean');
                }

                $process = $builder->getProcess();

                $process->run(function ($type, $data) use ($output) {
                    $output->write($data);
                });
            }
        }

        $output->writeln('<info>done!</info>');
    }

    /**
     * Returns Sonata route page generator service.
     *
     * @return RoutePageGenerator
     */
    private function getRoutePageGenerator()
    {
        return $this->getContainer()->get('sonata.page.route.page.generator');
    }
}
