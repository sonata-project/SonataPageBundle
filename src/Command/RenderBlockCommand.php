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

use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Migrates the name setting of all blocks into a code setting.
 *
 * @final since sonata-project/page-bundle 3.26
 */
class RenderBlockCommand extends Command
{
    protected static $defaultName = 'sonata:page:render-block';
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    public function configure(): void
    {
        $this->setDescription('Dump page information');
        $this->setHelp(
            <<<HELP
Dump page information

Available manager:
 - sonata.page.cms.snapshot
 - sonata.page.cms.page
HELP
        );

        $this->addArgument('manager', InputArgument::REQUIRED, 'The manager service id');
        $this->addArgument('page_id', InputArgument::REQUIRED, 'The page id');
        $this->addArgument('block_id', InputArgument::REQUIRED, 'The page id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $manager = $this->container->get($input->getArgument('manager'));

        if (!$manager instanceof CmsManagerInterface) {
            throw new \RuntimeException('The service does not implement the CmsManagerInterface');
        }

        $page = $manager->getPageById($input->getArgument('page_id'));

        $block = $manager->getBlock($input->getArgument('block_id'));

        if (!$block) {
            throw new \RuntimeException('Unable to find the related block');
        }

        $output->writeln('<info>Block Information</info>');
        $output->writeln(sprintf('  > Id: %d - type: %s - name: %s', $block->getId(), $block->getType(), $block->getName()));

        foreach ($block->getSettings() as $name => $value) {
            $output->writeln(sprintf('   >> %s: %s', $name, json_encode($value)));
        }

        $context = $this->container->get('sonata.block.context_manager')->get($block);

        $output->writeln("\n<info>BlockContext Information</info>");
        foreach ($context->getSettings() as $name => $value) {
            $output->writeln(sprintf('   >> %s: %s', $name, json_encode($value)));
        }

        $output->writeln("\n<info>Response Output</info>");

        // fake request
        $request = new Request();
        $this->container->enterScope('request');
        $this->container->set('request', $request, 'request');

        $output->writeln($this->container->get('sonata.block.renderer')->render($context));

        $this->container->leaveScope('request');

        return 0;
    }
}
