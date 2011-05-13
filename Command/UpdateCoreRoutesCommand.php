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

use Symfony\Bundle\FrameworkBundle\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

class UpdateCoreRoutesCommand extends Command
{

    public function configure()
    {
        $this->setName('sonata:page:update-core-routes');
        $this->setDescription('Update core routes, from routing files to page manager');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $router = $this->container->get('router.real');
        $manager = $this->getManager();
        $pageManager = $manager->getPageManager();

        foreach ($router->getRouteCollection()->all() as $name => $route) {
            $name = trim($name);
            $page = $pageManager->getPageByName($name);

            if (!$manager->isRouteNameDecorable($name)) {
                if ($page) {
                    $page->setEnabled(false);
                    $output->writeln(sprintf('<error>DISABLE</error> <error>% -50s</error> %s', $name, $route->getPattern()));
                } else {
                    continue;
                }
            }

            if (!$manager->isRouteUriDecorable($route->getPattern())) {
                if ($page) {
                    $page->setEnabled(false);
                    $output->writeln(sprintf('<error>DISABLE</error> % -50s <error>%s</error>', $name, $route->getPattern()));
                } else {
                    continue;
                }
            }

            $requirements = $route->getRequirements();
            if (isset($requirements['_method']) && $requirements['_method'] != 'GET') {
                if ($page) {
                    $page->setEnabled(false);
                    $output->writeln(sprintf('<error>DISABLE</error> % -50s <error>NOT GET METHOD</error>', $name));
                } else {
                    continue;
                }
            }

            $update = true;
            if (!$page) {
                $update = false;

                $output->writeln(sprintf('<info>CREATE</info>  % -50s %s', $name, $route->getPattern()));

                // todo : put some default value into the configuration file
                $page = $pageManager->createNewPage(array(
                    'template'      => $manager->getDefaultTemplate(),
                    'enabled'       => true,
                    'routeName'     => $name,
                    'name'          => $name,
                    'loginRequired' => false,
                    'slug'          => $route->getPattern(),
                ));
            }

            $page->setSlug($route->getPattern());
            $pageManager->save($page);

            $output->writeln(sprintf('<info>%s</info> % -50s %s', $update ? 'UPDATE ' : 'CREATE ', $name, $route->getPattern()));
        }
    }

    /**
     * @return \Sonata\PageBundle\Page\Manager
     */
    public function getManager()
    {
        return $this->container->get('sonata.page.manager');
    }
}