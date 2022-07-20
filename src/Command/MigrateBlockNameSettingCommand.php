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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrates the name setting of all blocks into a code setting.
 *
 * NEXT_MAJOR: Remove this class
 *
 * @deprecated since 3.27, and it will be removed in 4.0.
 */
final class MigrateBlockNameSettingCommand extends Command
{
    private const CONTAINER_TYPE = 'sonata.page.block.container';
    protected static $defaultName = 'sonata:page:migrate-block-setting';
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
    }

    public function configure(): void
    {
        $this->addOption(
            'class',
            null,
            InputOption::VALUE_OPTIONAL,
            'Block entity class',
            'Application\Sonata\PageBundle\Entity\Block'
        );
        $this->addOption(
            'update-name',
            null,
            InputOption::VALUE_NONE,
            'update name field from code setting'
        );
        $this->setDescription('Migrate the "name" setting of all blocks into a "code" setting and remove unused "orientation" setting on "'.self::CONTAINER_TYPE.'" blocks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = 0;
        $repository = $this->getRepository($input->getOption('class'));
        $blocks = $repository->findAll();

        foreach ($blocks as $block) {
            if (!$block instanceof BlockInterface) {
                throw new \Exception(
                    sprintf('The block class need to implements the %s interface.', BlockInterface::class)
                );
            }

            $settings = $block->getSettings();

            // Remove orientation option if it exists
            if (self::CONTAINER_TYPE === $block->getType() && \array_key_exists('orientation', $settings)) {
                unset($settings['orientation']);
                $block->setSettings($settings);

                $this->entityManager->persist($block);
                ++$count;
            }

            // only change rows that need to
            if (isset($settings['name'])) {
                // switch name with code key
                $settings['code'] = $settings['name'];
                unset($settings['name']);
                $block->setSettings($settings);

                // update name from code if option is set
                if (true === $input->getOption('update-name')) {
                    $block->setName($block->getSetting('code'));
                }

                $this->entityManager->persist($block);
                ++$count;
            }

            if ($count % 100) {
                $this->entityManager->flush();
            }
        }

        $this->entityManager->flush();

        $output->writeln("<info>Migrated $count blocks</info>");

        return 0;
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        @trigger_error(
            sprintf(
                'This %s is deprecated since sonata-project/page-bundle 3.27.0'.
                ' and it will be removed in 4.0',
                self::class
            ),
            \E_USER_DEPRECATED
        );

        return parent::run($input, $output);
    }

    /**
     * Returns the entity repository for given class name.
     *
     * @param string $class Entity class name
     *
     * @return EntityRepository
     */
    private function getRepository($class)
    {
        return $this->entityManager->getRepository($class);
    }
}
