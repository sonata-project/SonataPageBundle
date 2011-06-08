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

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class CacheFlushCommand extends Command
{
    public function configure()
    {
        $this->setName('sonata:page:cache-flush');
        $this->setDescription('Flush information');

        $this->addOption('service', null, InputOption::VALUE_OPTIONAL, 'Flush all elements related to the block servive', null);
        $this->addOption('keys', null, InputOption::VALUE_OPTIONAL, 'Flush all elements matching the providing keys (json format)', null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $input->getOption('service');
        $keys    = $input->getOption('keys');

        if ($service === null && $keys === null) {
            $output->writeln('<error>nothing to flush</error> - please provide a \'service\' or \'keys\' options');
            return;
        }

        $caches = $this->getManager()->getCacheServices();

        if ($service) {
            if (!isset($caches[$service])) {
                $output->writeln(sprintf('<error>unknown service</error> : %s', $service));
                $output->writeln('<comment>Services available</comment>');
                foreach (array_keys($caches) as $id) {
                    $output->writeln(sprintf(' > %s', $id));
                }

                return;
            }

            $output->write(sprintf('flushing <comment>%s</comment> ...', $service));
            $caches[$service]->flush(array(
                'block_type' => $service
            ));

            $output->writeln('<info>OK</info>');
        }

        if ($keys) {
            $keys = json_decode($keys, true);

            if (!is_array($keys)) {
                $output->writeln('<error>the provided keys cannot be decoded, please provide a valid json string</error>');
            }

            foreach($caches as $name => $cache) {
                $output->write(sprintf(' > %s : starting .... ', $name));
                $cache->flush($keys);
                $output->writeln('OK');
            }
        }


        $output->writeln('<info>done!</info>');
    }

    /**
     * @return \Sonata\PageBundle\Page\Manager
     */
    public function getManager()
    {
        return $this->container->get('sonata.page.manager');
    }
}