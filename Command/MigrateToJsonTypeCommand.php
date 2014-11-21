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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class MigrateToJsonTypeCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->setName('sonata:page:migrate-block-json');
        $this->addOption('table', null, InputOption::VALUE_OPTIONAL, 'Block table', 'page__block');
        $this->setDescription('Migrate all block settings to the doctrine JsonType');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $count = 0;
        $table = $input->getOption('table');
        $connection = $this->getContainer()->get('doctrine.orm.entity_manager')->getConnection();
        $blocks = $connection->fetchAll("SELECT * FROM $table");

        foreach ($blocks as $block) {
            // if the row need to migrate
            if (0 !== strpos($block['settings'], '{') && $block['settings'] !== '[]') {
                $block['settings'] = json_encode(unserialize($block['settings']));
                $connection->update($table, array('settings' => $block['settings']), array('id' => $block['id']));
                $count++;
            }
        }

        $output->writeln("Migrated $count blocks");
    }
}
