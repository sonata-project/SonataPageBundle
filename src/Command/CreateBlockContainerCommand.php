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
use Sonata\PageBundle\Model\PageManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates block containers for all pages.
 *
 * @author Christian Gripp <mail@core23.de>
 */
#[AsCommand(name: 'sonata:page:create-block-container', description: 'Creates a block container in all pages for specified template code')]
final class CreateBlockContainerCommand extends Command
{
    // TODO: Remove static properties when support for Symfony < 5.4 is dropped.
    protected static $defaultName = 'sonata:page:create-block-container';
    protected static $defaultDescription = 'Creates a block container in all pages for specified template code';

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
        \assert(null !== static::$defaultDescription);

        $this
            // TODO: Remove setDescription when support for Symfony < 5.4 is dropped.
            ->setDescription(static::$defaultDescription)
            ->addArgument('templateCode', InputArgument::REQUIRED, 'Template name according to sonata_page.yml (e.g. default)')
            ->addArgument('blockCode', InputArgument::REQUIRED, 'Block alias (e.g. content_bottom)')
            ->addArgument('blockName', InputArgument::OPTIONAL, 'Block name (e.g. Bottom container)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $blockCode = $input->getArgument('blockCode');

        $pageManager = $this->pageManager;

        $pages = $pageManager->findBy([
            'templateCode' => $input->getArgument('templateCode'),
        ]);

        foreach ($pages as $page) {
            $output->writeln(sprintf('Adding to page <info>%s</info>', $page->getName() ?? ''));

            $block = $this->blockInteractor->createNewContainer([
                'name' => $input->getArgument('blockName'),
                'enabled' => true,
                'page' => $page,
                'code' => $blockCode,
            ]);

            $page->addBlock($block);

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
