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

use Sonata\PageBundle\Model\BlockManagerInterface;
use Sonata\PageBundle\Model\PageBlockInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for cloning complete sites including all pages and blocks.
 *
 * @author Armin Weihbold <armin.weihbold@gmail.com>
 * @author Christian Gripp <mail@core23.de>
 */
#[AsCommand(name: 'sonata:page:clone-site', description: 'Clone a complete site including all their pages')]
final class CloneSiteCommand extends Command
{
    // TODO: Remove static properties when support for Symfony < 5.4 is dropped.
    protected static $defaultName = 'sonata:page:clone-site';
    protected static $defaultDescription = 'Clone a complete site including all their pages';

    private SiteManagerInterface $siteManager;
    private PageManagerInterface $pageManager;
    private BlockManagerInterface $blockManager;

    public function __construct(
        SiteManagerInterface $siteManager,
        PageManagerInterface $pageManager,
        BlockManagerInterface $blockManager
    ) {
        parent::__construct();

        $this->siteManager = $siteManager;
        $this->pageManager = $pageManager;
        $this->blockManager = $blockManager;
    }

    public function configure(): void
    {
        \assert(null !== static::$defaultDescription);

        $this
            // TODO: Remove setDescription when support for Symfony < 5.4 is dropped.
            ->setDescription(static::$defaultDescription)
            ->addOption('source-id', 'so', InputOption::VALUE_REQUIRED, 'Source site id')
            ->addOption('dest-id', 'd', InputOption::VALUE_REQUIRED, 'Destination site id')
            ->addOption('prefix', 'p', InputOption::VALUE_REQUIRED, 'Title prefix')
            ->addOption('only-hybrid', 'oh', InputOption::VALUE_REQUIRED, 'only clone hybrid pages', false);
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if (null === $input->getOption('source-id')) {
            $this->listAllSites($output);

            throw new \InvalidArgumentException('Please provide a "--source-id=SITE_ID" option.');
        }

        if (null === $input->getOption('dest-id')) {
            $this->listAllSites($output);

            throw new \InvalidArgumentException('Please provide a "--dest-id=SITE_ID" option.');
        }

        if (null === $input->getOption('prefix')) {
            throw new \InvalidArgumentException('Please provide a "--prefix=PREFIX" option.');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (false !== $input->getOption('only-hybrid')) {
            $output->writeln('Cloning hybrid pages only.');
            $hybridOnly = true;
        } else {
            $hybridOnly = false;
        }

        /** @var SiteInterface $sourceSite */
        $sourceSite = $this->siteManager->find($input->getOption('source-id'));

        /** @var SiteInterface $destSite */
        $destSite = $this->siteManager->find($input->getOption('dest-id'));

        $pageClones = [];
        $blockClones = [];

        $output->writeln('Cloning pages');
        /** @var PageInterface[] $pages */
        $pages = $this->pageManager->findBy([
            'site' => $sourceSite,
        ]);
        foreach ($pages as $page) {
            if ($hybridOnly && !$page->isHybrid()) {
                continue;
            }

            $output->writeln(sprintf(
                ' % 4s - % -70s - % 4s',
                $page->getId(),
                $page->getTitle(),
                null !== $page->getParent() ? $page->getParent()->getId() : ''
            ));

            $newPage = clone $page;

            if ('' !== $newPage->getTitle()) {
                $newPage->setTitle($input->getOption('prefix').$newPage->getTitle());
            }

            $newPage->setSite($destSite);
            $this->pageManager->save($newPage);

            $pageClones[$page->getId()] = $newPage;

            // Clone page blocks
            /** @var PageBlockInterface[] $blocks */
            $blocks = $this->blockManager->findBy([
                'page' => $page,
            ]);
            foreach ($blocks as $block) {
                $output->writeln(sprintf(' cloning block % 4s ', $block->getId()));
                $newBlock = clone $block;
                $newBlock->setPage($newPage);
                $blockClones[$block->getId()] = $newBlock;
                $this->blockManager->save($newBlock);
            }
        }

        $output->writeln('Fixing page parents');
        foreach ($pageClones as $page) {
            if (null !== $page->getParent()) {
                $id = $page->getParent()->getId();
                \assert(null !== $id);

                if (\array_key_exists($id, $pageClones)) {
                    $output->writeln(sprintf(
                        'new parent: % 4s - % -70s - % 4s -> % 4s',
                        $page->getId(),
                        $page->getTitle(),
                        $id,
                        $pageClones[$id]->getId()
                    ));
                    $page->setParent($pageClones[$id]);
                } else {
                    $page->setParent(null);
                }
            }

            $this->pageManager->save($page);
        }

        $output->writeln('Fixing block parents');
        foreach ($pageClones as $page) {
            $blocks = $this->blockManager->findBy([
                'page' => $page,
            ]);
            foreach ($blocks as $block) {
                if (null !== $block->getParent()) {
                    $id = $block->getParent()->getId();
                    \assert(null !== $id);

                    $output->writeln(sprintf(
                        'new block parent: % 4s - % 4s',
                        $block->getId(),
                        $blockClones[$id]->getId()
                    ));

                    if (\array_key_exists($id, $blockClones)) {
                        $block->setParent($blockClones[$id]);
                    } else {
                        $block->setParent(null);
                    }

                    $this->blockManager->save($block);
                }
            }
        }

        $output->writeln('<info>done!</info>');

        return 0;
    }

    /**
     * Prints a list of all available sites.
     */
    private function listAllSites(OutputInterface $output): void
    {
        $output->writeln(sprintf(' % 5s - % -30s - %s', 'ID', 'Name', 'Url'));

        $sites = $this->siteManager->findAll();

        foreach ($sites as $site) {
            $output->writeln(sprintf(' % 5s - % -30s - %s', $site->getId(), $site->getName(), $site->getUrl()));
        }
    }
}
