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

class UpdateCoreRoutesCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('sonata:page:update-core-routes');
        $this->setDescription('Update core routes, from routing files to page manager');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $router      = $this->getContainer()->get('router');
        $cmsManager  = $this->getManager();
        $pageManager = $cmsManager->getPageManager();

        $output->writeln(array(
            "<comment>Updating/Creating hybrid pages</comment>",
            str_repeat('-', 80)
        ));

        $knowRoutes = array();
        foreach ($router->getRouteCollection()->all() as $name => $route) {
            $name = trim($name);

            $knowRoutes[] = $name;

            $page = $pageManager->getPageByName($name);

            if (
                    !$cmsManager->isRouteNameDecorable($name)
                    || !$cmsManager->isRouteUriDecorable($route->getPattern())
            ) {
                if ($page) {
                    $page->setEnabled(false);
                    $output->writeln(sprintf('<error>DISABLE</error> <error>% -50s</error> %s', $name, $route->getPattern()));
                } else {
                    continue;
                }
            }

            $update = true;
            if (!$page) {
                $update = false;

                $requirements = $route->getRequirements();

                $params = array(
                    'routeName'     => $name,
                    'name'          => $name,
                    'url'           => $route->getPattern(),
                    'requestMethod' => isset($requirements['_method']) ? $requirements['_method'] : '',
                );
                $params = array_merge($params, $cmsManager->getCreateNewPageDefaultsByName($name));

                $page = $pageManager->createNewPage($params);
            }

            $page->setSlug($route->getPattern());
            $pageManager->save($page);

            $output->writeln(sprintf('<info>%s</info> % -50s %s', $update ? 'UPDATE ' : 'CREATE ', $name, $route->getPattern()));
        }

        $has = false;
        foreach ($pageManager->getHybridPages() as $page) {
            if (!$page->isHybrid() || $page->isInternal()) {
                continue;
            }

            if (!in_array($page->getRouteName(), $knowRoutes)) {
                if (!$has) {
                    $has = true;
                    $output->writeln(array(
                        '',
                        'Some hybrid pages does not exist anymore',
                         str_repeat('-', 80)
                    ));
                }

                $output->writeln(sprintf('<error>ERROR</error>   %s', $page->getRouteName()));
            }
        }

        if ($has) {
            $output->writeln(<<<MSG
<error>
  *WARNING* : Pages has been updated however some pages do not exist anymore.
              You must remove them manually.
</error>
MSG
);
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