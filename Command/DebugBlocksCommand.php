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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class DebugBlocksCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:page:block-debug');
        $this->setDescription('Debug all blocks available, show default settings of each block');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $cmsManager     = $this->getManager();
        $blockServices  = $cmsManager->getBlockServices();

        foreach ($blockServices as $code => $service) {
            $settings = $service->getDefaultSettings();

            $output->writeln('');
            $output->writeln(sprintf('<info>>> %s</info>', $service->getName()));
            $output->writeln(sprintf('<comment>%s:</comment>', $code));

            foreach ($settings as $key => $val) {
                $output->writeln(sprintf('    %s:%s%s', $key, str_repeat(' ', 20 - strlen($key)), $val));
            }
        }
    }

    /**
     * @return \Sonata\PageBundle\CmsManager\CmsPageManager
     */
    public function getManager()
    {
        return $this->getContainer()->get('sonata.page.cms.page');
    }
}
