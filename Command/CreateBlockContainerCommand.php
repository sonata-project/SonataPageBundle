<?php

namespace Sonata\PageBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateBlockContainerCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('sonata:page:create-block-container')
            ->setDescription('Creates a block container in all pages for specified template code')
            ->addArgument('templateCode', InputArgument::REQUIRED, 'Template name according to sonata_page.yml (e.g. default)')
            ->addArgument('blockCode', InputArgument::REQUIRED, 'Block alias (e.g. content_bottom)')
            ->addArgument('blockName', InputArgument::OPTIONAL, 'Block name (e.g. Bottom container)')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $templateCode = $input->getArgument('templateCode');
        $blockCode = $input->getArgument('blockCode');
        $blockName = $input->getArgument('blockName');

        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $pages = $pageManager->findBy(['templateCode' => $templateCode]);

        foreach ($pages as $page) {
            $output->writeln(sprintf('Adding to page <info>%s</info>', $page->getName()));

            $page->addBlocks($block = $blockInteractor->createNewContainer([
                'enabled' => true,
                'page'    => $page,
                'code'    => $blockCode,
            ]));
            if ($blockName) {
                $block->setName($blockName);
                $blockManager->save($block);
            }
            $pageManager->save($page);
        }

        $output->writeln('');
        $output->writeln(sprintf('Don\'t forget to add block <comment>%s</comment> into your <comment>sonata_page.yml</comment>', $blockCode));
        $output->writeln('');

        $output->writeln('<info>done!</info>');
    }

    /**
     * @return \Sonata\PageBundle\Entity\BlockInteractor
     */
    public function getBlockInteractor()
    {
        return $this->getContainer()->get('sonata.page.block_interactor');
    }
}
