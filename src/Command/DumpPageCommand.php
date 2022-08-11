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

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrates the name setting of all blocks into a code setting.
 *
 * @final since sonata-project/page-bundle 3.26
 *
 * NEXT_MAJOR: Remove this class
 *
 * @deprecated since 3.27, and it will be removed in 4.0.
 */
class DumpPageCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('sonata:page:dump-page');
        $this->setDescription('Dump page information');
        $this->setHelp(
            <<<HELP
                Dump page information

                Available managers:
                 - sonata.page.cms.snapshot
                 - sonata.page.cms.page

                You can use the --extended option to dump block configuration
                HELP
        );

        $this->addArgument('manager', InputArgument::REQUIRED, 'The manager service id');
        $this->addArgument('page_id', InputArgument::REQUIRED, 'The page id');
        $this->addOption('extended', null, InputOption::VALUE_NONE, 'Extended information');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get($input->getArgument('manager'));

        if (!$manager instanceof CmsManagerInterface) {
            throw new \RuntimeException('The service does not implement the CmsManagerInterface');
        }

        $page = $manager->getPageById($input->getArgument('page_id'));

        $output->writeln([
            '<info>Page:</info>',
            sprintf(' > Id: %s - %s - type: %s', $page->getId(), $page->getEnabled() ? 'enabled' : 'disabled', $page->getType()),
            sprintf(' > Name: %s', $page->getName()),
            sprintf(' > Route: %s', $page->getRouteName()),
            sprintf(' > Slug: %s', $page->getSlug()),
            sprintf(' > Url: %s (%s)', $page->getUrl(), $page->getRequestMethod()),
            sprintf(' > Template: %s (%s)', $page->getTemplateCode(), $page->getDecorate() ? 'decorate' : 'standalone'),
            sprintf(' > Kind: %s ', $page->isCms() ? 'cms' : ($page->isDynamic() ? 'dynamic' : ($page->isHybrid() ? 'hybrid' : 'unknown'))),
            sprintf(' > Class: %s ', \get_class($page)),
            '',
            '<info>Blocks:</info>',
        ]);

        foreach ($page->getBlocks() as $block) {
            $this->renderBlock($block, $output, $input->getOption('extended'));
        }

        return 0;
    }

    /**
     * @param bool $extended
     * @param int  $space
     */
    public function renderBlock(BlockInterface $block, OutputInterface $output, $extended, $space = 0)
    {
        $output->writeln(sprintf(
            '%s <comment>> Id: %d - type: %s - name: %s</comment>',
            str_repeat('  ', $space),
            $block->getId(),
            $block->getType(),
            $block->getName()
        ));

        if ($extended) {
            $output->writeln(sprintf('%s page class: <comment>%s</comment>', str_repeat('  ', $space + 1), \get_class($block->getPage())));
            foreach ($block->getSettings() as $name => $value) {
                $output->writeln(sprintf('%s %s: %s', str_repeat('  ', $space + 1), $name, var_export($value, 1)));
            }
        }

        foreach ($block->getChildren() as $block) {
            $this->renderBlock($block, $output, $extended, $space + 1);
        }
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
}
