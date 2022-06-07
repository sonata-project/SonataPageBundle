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
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\CmsManager\CmsPageManager;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Model\SnapshotManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrates the name setting of all blocks into a code setting.
 *
 * @final since sonata-project/page-bundle 3.26
 */
class DumpPageCommand extends BaseCommand
{
    /** @var CmsManagerInterface */
    private $cmsSnapshotManager;

    public function __construct(
        SiteManagerInterface $siteManager,
        PageManagerInterface $pageManager,
        SnapshotManagerInterface $snapshotManager,
        ManagerInterface $blockManager,
        CmsPageManager $cmsPageManager,
        ExceptionListener $exceptionListener,
        BackendInterface $backend,
        BackendInterface $backendRuntime,
        CmsManagerInterface $cmsSnapshotManager
    ) {
        parent::__construct(
            $siteManager,
            $pageManager,
            $snapshotManager,
            $blockManager,
            $cmsPageManager,
            $exceptionListener,
            $backend,
            $backendRuntime
        );
        $this->cmsSnapshotManager = $cmsSnapshotManager;
    }

    public function configure(): void
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

    /**
     * @param bool $extended
     * @param int  $space
     */
    public function renderBlock(BlockInterface $block, OutputInterface $output, $extended, $space = 0): void
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $manager = $input->getArgument('manager');

        if (!\in_array($manager, ['sonata.page.cms.snapshot', 'sonata.page.cms.page'], true)) {
            throw new \RuntimeException(
                'Available managers are "sonata.page.cms.snapshot" and "sonata.page.cms.page"'
            );
        }

        $managerService = 'sonata.page.cms.snapshot' === $manager ? $this->cmsSnapshotManager : $this->cmsPageManager;

        $page = $managerService->getPageById($input->getArgument('page_id'));

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
}
