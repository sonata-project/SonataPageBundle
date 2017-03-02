<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\Sonata\PageBundle\Command;

use Sonata\PageBundle\Command\BaseCommand as BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clone Pages.
 *
 * @author Armin Weihbold <armin.weihbold@gmail.com>
 */
class ClonePagesCommand extends BaseCommand
{
    /**
   * {@inheritdoc}
   */
  public function configure()
  {
      $this->setName('sonata:page:clone');
      $this->setDescription('Clone pages');
      $this->addOption('sourcesite', null, InputOption::VALUE_REQUIRED, 'Source Site id', null);
      $this->addOption('destsite', null, InputOption::VALUE_REQUIRED, 'Dest Site id', null);
      $this->addOption('prefix', null, InputOption::VALUE_REQUIRED, 'title prefix', null);
      $this->addOption('only-hybrid', null, InputOption::VALUE_OPTIONAL, 'only clone hybrid pages', null);
      $this->addOption('additional', null, InputOption::VALUE_OPTIONAL, 'clone additional pages (csv ids)', null);
      $this->addOption('base-console', null, InputOption::VALUE_OPTIONAL, 'Base symfony console command', 'php app/console');
  }

  /**
   * {@inheritdoc}
   */
  public function execute(InputInterface $input, OutputInterface $output)
  {
      if (!$input->getOption('sourcesite')) {
          $output->writeln('Please provide an <info>--sourcesite=SITE_ID</info> option\n');

          $output->writeln(sprintf(' % 5s - % -30s - %s', 'ID', 'Name', 'Url'));

          foreach ($this->getSiteManager()->findBy(array()) as $site) {
              $output->writeln(sprintf(' % 5s - % -30s - %s', $site->getId(), $site->getName(), $site->getUrl()));
          }

          return;
      }

      if (!$input->getOption('destsite')) {
          $output->writeln('Please provide an <info>--destsitesite=SITE_ID</info> option\n');

          $output->writeln(sprintf(' % 5s - % -30s - %s', 'ID', 'Name', 'Url'));

          foreach ($this->getSiteManager()->findBy(array()) as $site) {
              $output->writeln(sprintf(' % 5s - % -30s - %s', $site->getId(), $site->getName(), $site->getUrl()));
          }

          return;
      }

      if (!$input->getOption('prefix')) {
          $output->writeln('Please provide a <info>--prefix=PREFIX</info> option\n');

          return;
      }

      if ($input->getOption('only-hybrid')) {
          $output->writeln('Only cloning hybrid pages.\n');
          $hybridOnly = true;
      } else {
          $hybridOnly = false;
      }

      if ($input->getOption('additional')) {
          // TODO: sanity check input
      $output->writeln('Cloning additional pages');
          $additional_page_ids = preg_split('/,/', $input->getOption('additional'));
          for ($i = 0; $i < count($additional_page_ids); ++$i) {
              $additional_page_ids[ $i ] = intval($additional_page_ids[ $i ]);
          }
      } else {
          $additional_page_ids = array();
      }

      $source_site = $this->getSiteManager()->findBy(array('id' => $input->getOption('sourcesite')));
      $dest_site = $this->getSiteManager()->findBy(array('id' => $input->getOption('destsite')));

      $pages = $this->getPageManager()->findBy(array('site' => $source_site[0]));

      $pageClones = array();

      $blockClones = array();

    // TODO: check if we are missing parents
    $output->writeln('Cloning pages\n');
      foreach ($pages as $page) {
          if ($hybridOnly && !$page->isHybrid() && !in_array($page->getId(), $additional_page_ids)) {
              continue;
          }
          $output->writeln(sprintf(' % 4s - % -70s - % 4s', $page->getId(), $page->getTitle(), $page->getParent() ? $page->getParent()->getId() : ''));
          $newPage = clone $page;

          if ($newPage->getTitle() != '') {
              $newPage->setTitle($input->getOption('prefix').$newPage->getTitle());
          }

          $pageClones[ $page->getId() ] = $newPage;
          $newPage->setSite($dest_site[0]);
          $this->getPageManager()->save($newPage);

      // clone page blocks
      $blocks = $this->getBlockManager()->findBy(array('page' => $page));
          foreach ($blocks as $block) {
              if ($block->getType() == 'sonata.media.block.gallery') {
                  continue;
              }
              $output->writeln(sprintf(' cloning block % 4s ', $block->getId()));
              $newBlock = clone $block;
              $newBlock->setPage($newPage);
              $blockClones[ $block->getId() ] = $newBlock;
              $this->getBlockManager()->save($newBlock);
          }
      }
      $output->writeln('Fixing parents and targets\n');
      foreach ($pageClones as $page) {
          if ($page->getParent()) {
              $output->writeln(sprintf('new parent: % 4s - % -70s - % 4s -> % 4s', $page->getId(), $page->getTitle(), $page->getParent() ? $page->getParent()->getId() : '', $pageClones[ $page->getParent()->getId() ]->getId()));
              $page->setParent($pageClones[ $page->getParent()->getId() ]);
          }

          if ($page->getTarget()) {
              $output->writeln(sprintf('new target: % 4s - % -70s - % 4s', $page->getId(), $page->getTitle(), $page->getParent() ? $page->getParent()->getId() : ''));
              $page->setTarget($pageClones[ $page->getTarget()->getId() ]);
          }
          $this->getPageManager()->save($newPage, true);
      }

      $output->writeln('Fixing block parents\n');
      foreach ($pageClones as $page) {
          $blocks = $this->getBlockManager()->findBy(array('page' => $page));
          foreach ($blocks as $block) {
              if ($block->getType() == 'sonata.media.block.gallery') {
                  continue;
              }
              if ($block->getParent()) {
                  $output->writeln(sprintf('new block parent: % 4s - % 4s', $block->getId(), $blockClones[ $block->getParent()->getId() ]->getId()));
                  $block->setParent($blockClones[ $block->getParent()->getId() ]);
              }
              $this->getBlockManager()->save($newBlock, true);
          }
      }

      $output->writeln('<info>done!</info>');
  }
}
