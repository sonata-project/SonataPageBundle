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

use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Migrates the name setting of all blocks into a code setting
 */
class RenderBlockCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('sonata:page:render-block');
        $this->setDescription('Dump page information');
        $this->setHelp(
'Dump page information

Available manager:
 - sonata.page.cms.snapshot
 - sonata.page.cms.page
');

        $this->addArgument('manager', InputArgument::REQUIRED, 'The manager service id');
        $this->addArgument('page_id', InputArgument::REQUIRED, 'The page id');
        $this->addArgument('block_id', InputArgument::REQUIRED, 'The page id');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get($input->getArgument('manager'));

        if (!$manager instanceof CmsManagerInterface) {
            throw new \RuntimeException('The service does not implement the CmsManagerInterface');
        }

        $page = $manager->getPageById($input->getArgument('page_id'));

        $block = $manager->getBlock($input->getArgument('block_id'));

        if (!$block) {
            throw new \RuntimeException('Unable to find the related block');
        }

        $output->writeln("<info>Block Information</info>");
        $output->writeln(sprintf("  > Id: %d - type: %s - name: %s", $block->getId(), $block->getType(), $block->getName()));

        foreach ($block->getSettings() as $name => $value) {
            $output->writeln(sprintf("   >> %s: %s", $name, json_encode($value)));
        }

        $context = $this->getContainer()->get('sonata.block.context_manager')->get($block);

        $output->writeln("\n<info>BlockContext Information</info>");
        foreach ($context->getSettings() as $name => $value) {
            $output->writeln(sprintf("   >> %s: %s", $name, json_encode($value)));
        }

        $output->writeln("\n<info>Response Output</info>");

        // fake request
        $request = new Request();
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', $request, 'request');

        $output->writeln($this->getContainer()->get('sonata.block.renderer')->render($context));
    }
}