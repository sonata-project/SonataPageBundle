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

use Sonata\PageBundle\Model\BlockInteractorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates block containers for all pages.
 *
 * @author Christian Gripp <mail@core23.de>
 */
final class CreateBlockContainerCommand extends Command
{
    private PageManagerInterface $pageManager;
    private BlockInteractorInterface $blockInteractor;

    public function __construct(PageManagerInterface $pageManager, BlockInteractorInterface $blockInteractor)
    {
        parent::__construct();

        $this->pageManager = $pageManager;
        $this->blockInteractor = $blockInteractor;
    }

    protected function configure(): void
    {
        $this->setName('sonata:page:create-block-container');

        $this->addArgument('templateCode', InputArgument::REQUIRED, 'Template name according to sonata_page.yml (e.g. default)');
        $this->addArgument('blockCode', InputArgument::REQUIRED, 'Block alias (e.g. content_bottom)');
        $this->addArgument('blockName', InputArgument::OPTIONAL, 'Block name (e.g. Bottom container)');

        $this->setDescription('Creates a block container in all pages for specified template code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $blockCode = $input->getArgument('blockCode');

        $pageManager = $this->pageManager;

        $pages = $pageManager->findBy([
            'templateCode' => $input->getArgument('templateCode'),
        ]);

        /** @var PageInterface $page */
        foreach ($pages as $page) {
            $output->writeln(sprintf('Adding to page <info>%s</info>', $page->getName()));

            $block = $this->blockInteractor->createNewContainer([
                'name' => $input->getArgument('blockName'),
                'enabled' => true,
                'page' => $page,
                'code' => $blockCode,
            ]);

            $page->addBlocks($block);

            $pageManager->save($page);
        }

        $output->writeln(sprintf(
            'Don\'t forget to add block <comment>%s</comment> into your <comment>sonata_page.yml</comment>',
            $blockCode
        ));

        $output->writeln('<info>done!</info>');

        return 0;
    }
}
