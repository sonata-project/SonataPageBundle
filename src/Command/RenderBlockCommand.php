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

use Sonata\BlockBundle\Block\BlockContextManagerInterface;
use Sonata\BlockBundle\Block\BlockRendererInterface;
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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Migrates the name setting of all blocks into a code setting.
 *
 * @final since sonata-project/page-bundle 3.26
 */
class RenderBlockCommand extends BaseCommand
{
    /** @var CmsManagerInterface */
    private $cmsSnapshotManager;

    /** @var BlockContextManagerInterface */
    private $blockContextManager;

    /** @var BlockRendererInterface */
    private $blockRenderer;

    public function __construct(
        SiteManagerInterface $siteManager,
        PageManagerInterface $pageManager,
        SnapshotManagerInterface $snapshotManager,
        ManagerInterface $blockManager,
        CmsPageManager $cmsPageManager,
        ExceptionListener $exceptionListener,
        BackendInterface $backend,
        BackendInterface $backendRuntime,
        CmsManagerInterface $cmsSnapshotManager,
        BlockContextManagerInterface $blockContextManager,
        BlockRendererInterface $blockRenderer
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
        $this->blockContextManager = $blockContextManager;
        $this->blockRenderer = $blockRenderer;
    }

    public function configure(): void
    {
        $this->setName('sonata:page:render-block');
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
        $this->addArgument('block_id', InputArgument::REQUIRED, 'The block id');
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

        $block = $managerService->getBlock($input->getArgument('block_id'));

        if (!$block) {
            throw new \RuntimeException('Unable to find the related block');
        }

        $output->writeln('<info>Block Information</info>');
        $output->writeln(sprintf('  > Id: %d - type: %s - name: %s', $block->getId(), $block->getType(), $block->getName()));

        foreach ($block->getSettings() as $name => $value) {
            $output->writeln(sprintf('   >> %s: %s', $name, json_encode($value)));
        }

        $context = $this->blockContextManager->get($block);

        $output->writeln("\n<info>BlockContext Information</info>");
        foreach ($context->getSettings() as $name => $value) {
            $output->writeln(sprintf('   >> %s: %s', $name, json_encode($value)));
        }

        $output->writeln("\n<info>Response Output</info>");

        // fake request
        $output->writeln($this->blockRenderer->render($context));

        return 0;
    }
}
