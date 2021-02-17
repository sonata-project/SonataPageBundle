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

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateToJsonTypeCommand extends BaseCommand
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(
        ?string $name = null,
        ContainerInterface $container,
        EntityManager $entityManager
    ) {
        parent::__construct($name, $container);

        $this->entityManager = $entityManager;
    }

    public function configure()
    {
        $this->setName('sonata:page:migrate-block-json');
        $this->addOption('table', null, InputOption::VALUE_OPTIONAL, 'Block table', 'page__block');
        $this->setDescription('Migrate all block settings to the doctrine JsonType');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $count = 0;
        $table = $input->getOption('table');
        $connection = $this->entityManager->getConnection();
        $blocks = $connection->fetchAll("SELECT * FROM $table");

        foreach ($blocks as $block) {
            // if the row need to migrate
            if (0 !== strpos($block['settings'], '{') && '[]' !== $block['settings']) {
                $block['settings'] = json_encode(unserialize($block['settings']));
                $connection->update($table, ['settings' => $block['settings']], ['id' => $block['id']]);
                ++$count;
            }
        }

        $output->writeln("Migrated $count blocks");
    }
}
