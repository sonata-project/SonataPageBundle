<?php

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
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for cloning complete sites including all pages and blocks.
 *
 * @author Armin Weihbold <armin.weihbold@gmail.com>
 * @author Christian Gripp <mail@core23.de>
 */
final class CloneSiteCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('sonata:page:clone-site')
            ->setDescription('Clone a complete site including all their pages')
            ->addOption('source-id', 'so', InputOption::VALUE_REQUIRED, 'Source site id', null)
            ->addOption('dest-id', 'd', InputOption::VALUE_REQUIRED, 'Destination site id', null)
            ->addOption('prefix', 'p', InputOption::VALUE_REQUIRED, 'Title prefix', null)
            ->addOption('only-hybrid', 'oh', InputOption::VALUE_OPTIONAL, 'only clone hybrid pages', false);
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('source-id')) {
            $this->listAllSites($output);

            throw new \InvalidArgumentException('Please provide a "--source-id=SITE_ID" option.');
        }

        if (!$input->getOption('dest-id')) {
            $this->listAllSites($output);

            throw new \InvalidArgumentException('Please provide a "--dest-id=SITE_ID" option.');
        }

        if (!$input->getOption('prefix')) {
            throw new \InvalidArgumentException('Please provide a "--prefix=PREFIX" option.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('only-hybrid')) {
            $output->writeln('Cloning hybrid pages only.');
            $hybridOnly = true;
        } else {
            $hybridOnly = false;
        }

        /** @var SiteInterface $sourceSite */
        $sourceSite = $this->getSiteManager()->find($input->getOption('source-id'));

        /** @var SiteInterface $destSite */
        $destSite = $this->getSiteManager()->find($input->getOption('dest-id'));

        $pageClones = [];
        $blockClones = [];

        $output->writeln('Cloning pages');
        /** @var PageInterface[] $pages */
        $pages = $this->getPageManager()->findBy([
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
                $page->getParent() ? $page->getParent()->getId() : ''
            ));

            $newPage = clone $page;

            if ('' != $newPage->getTitle()) {
                $newPage->setTitle($input->getOption('prefix').$newPage->getTitle());
            }

            $newPage->setSite($destSite);
            $this->getPageManager()->save($newPage);

            $pageClones[$page->getId()] = $newPage;

            // Clone page blocks
            /** @var BlockInterface[] $blocks */
            $blocks = $this->getBlockManager()->findBy([
                'page' => $page,
            ]);
            foreach ($blocks as $block) {
                $output->writeln(sprintf(' cloning block % 4s ', $block->getId()));
                $newBlock = clone $block;
                $newBlock->setPage($newPage);
                $blockClones[$block->getId()] = $newBlock;
                $this->getBlockManager()->save($newBlock);
            }
        }

        $output->writeln('Fixing page parents and targets');
        foreach ($pageClones as $page) {
            if ($page->getParent()) {
                if (array_key_exists($page->getParent()->getId(), $pageClones)) {
                    $output->writeln(sprintf(
                        'new parent: % 4s - % -70s - % 4s -> % 4s',
                        $page->getId(),
                        $page->getTitle(),
                        $page->getParent() ? $page->getParent()->getId() : '',
                        $pageClones[$page->getParent()->getId()]->getId()
                    ));
                    $page->setParent($pageClones[$page->getParent()->getId()]);
                } else {
                    $page->setParent(null);
                }
            }

            if ($page->getTarget()) {
                if (array_key_exists($page->getTarget()->getId(), $pageClones)) {
                    $output->writeln(
                        sprintf(
                            'new target: % 4s - % -70s - % 4s',
                            $page->getId(),
                            $page->getTitle(),
                            $page->getParent() ? $page->getParent()->getId() : ''
                        )
                    );
                    $page->setTarget($pageClones[$page->getTarget()->getId()]);
                } else {
                    $page->setTarget(null);
                }
            }

            $this->getPageManager()->save($newPage, true);
        }

        $output->writeln('Fixing block parents');
        foreach ($pageClones as $page) {
            $blocks = $this->getBlockManager()->findBy([
                'page' => $page,
            ]);
            foreach ($blocks as $block) {
                if ($block->getParent()) {
                    $output->writeln(sprintf(
                        'new block parent: % 4s - % 4s',
                        $block->getId(),
                        $blockClones[$block->getParent()->getId()]->getId()
                    ));

                    if (array_key_exists($block->getParent()->getId(), $blockClones)) {
                        $block->setParent($blockClones[$block->getParent()->getId()]);
                    } else {
                        $block->setParent(null);
                    }

                    $this->getBlockManager()->save($block, true);
                }
            }
        }

        $output->writeln('<info>done!</info>');
    }

    /**
     * Prints a list of all available sites.
     *
     * @param OutputInterface $output
     */
    private function listAllSites(OutputInterface $output)
    {
        $output->writeln(sprintf(' % 5s - % -30s - %s', 'ID', 'Name', 'Url'));

        $sites = $this->getSiteManager()->findAll();

        foreach ($sites as $site) {
            $output->writeln(sprintf(' % 5s - % -30s - %s', $site->getId(), $site->getName(), $site->getUrl()));
        }
    }
}
