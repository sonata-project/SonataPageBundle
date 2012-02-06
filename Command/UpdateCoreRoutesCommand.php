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
use Sonata\PageBundle\Model\SiteInterface;

use Symfony\Component\Process\Process;

class UpdateCoreRoutesCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('sonata:page:update-core-routes');
        $this->setDescription('Update core routes, from routing files to page manager');
        $this->addOption('all', null, InputOption::VALUE_NONE, 'Create snapshots for all sites');
        $this->addOption('site', null, InputOption::VALUE_OPTIONAL, 'Site id', null);
        $this->addOption('base-command', null, InputOption::VALUE_OPTIONAL, 'Site id', 'php app/console');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('site') && !$input->getOption('all')) {
             $output->writeln('Please provide an <info>--site=SITE_ID</info> option or the <info>--all</info> directive');
             $output->writeln('');

             $output->writeln(sprintf(" % 5s - % -30s - %s", "ID", "Name", "Url"));

             foreach ($this->getSiteManager()->findBy() as $site) {
                 $output->writeln(sprintf(" % 5s - % -30s - %s", $site->getId(), $site->getName(), $site->getUrl()));
             }

             return;
         }

         foreach ($this->getSites($input) as $site) {
             if ($input->getOption('site') != 'all') {
                 $this->updateRoutes($site, $output);
                 $output->writeln("");
             } else {

                 $p = new Process(sprintf('%s sonata:page:update-core-routes --env=%s --site=%s %s', $input->getOption('base-command'), $input->getOption('env'), $site->getId(), $input->getOption('no-debug') ? '--no-debug' : ''));

                 $p->run(function($type, $data) use($output) {
                     $output->write($data);
                 });
             }
         }


        $output->writeln("<info>done!</info>");
    }

    /**
     * @param \Sonata\PageBundle\Model\SiteInterface $site
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    private function updateRoutes(SiteInterface $site, OutputInterface $output)
    {
        $router      = $this->getContainer()->get('router');
        $cmsManager  = $this->getCmsPageManager();
        $pageManager = $cmsManager->getPageManager();

        $message = sprintf(" > <info>Updating core routes for site</info> : <comment>%s - %s</comment>", $site->getName(), $site->getUrl());

        $output->writeln(array(
            str_repeat('=', strlen($message)),
            "",
            $message,
            "",
            str_repeat('=', strlen($message)),
        ));

        $knowRoutes = array();
        foreach ($router->getRouteCollection()->all() as $name => $route) {
            $name = trim($name);

            $knowRoutes[] = $name;

            $page = $pageManager->getPageByName($site, $name);

            if (!$cmsManager->isRouteNameDecorable($name) || !$cmsManager->isRouteUriDecorable($route->getPattern())) {
                if ($page) {
                    $page->setEnabled(false);
                    $output->writeln(sprintf('  <error>DISABLE</error> <error>% -50s</error> %s', $name, $route->getPattern()));
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
                );
                $params = array_merge($params, $cmsManager->getCreateNewPageDefaultsByName($name));

                $page = $pageManager->createNewPage($params);
                $page->setSite($site);
                $page->setRequestMethod(isset($requirements['_method']) ? $requirements['_method'] : 'GET|POST|HEAD|DELETE|PUT');
            }

            $page->setSlug($route->getPattern());
            $pageManager->save($page);

            $output->writeln(sprintf('  <info>%s</info> % -50s %s', $update ? 'UPDATE ' : 'CREATE ', $name, $route->getPattern()));
        }

        foreach ($cmsManager->getHttpErrorCodes() as $name) {
            $name = trim($name);

            $knowRoutes[] = $name;

            $page = $pageManager->getPageByName($site, $name);

            if (!$page) {

                $params = array(
                    'routeName'     => $name,
                    'name'          => $name,
                    'url'           => "/".$name,
                );
                $params = array_merge($params, $cmsManager->getCreateNewPageDefaultsByName($name));

                $params['decorate'] = false;

                $page = $pageManager->createNewPage($params);
                $page->setSite($site);
                $page->setRequestMethod(isset($requirements['_method']) ? $requirements['_method'] : 'GET|POST|HEAD|DELETE|PUT');
                
                $pageManager->save($page);
                $output->writeln(sprintf('  <info>%s</info> % -50s %s', 'CREATE ', $name, ''));
            }
        }



        $has = false;
        foreach ($pageManager->getHybridPages($site) as $page) {
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

                $output->writeln(sprintf('  <error>ERROR</error>   %s', $page->getRouteName()));
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
}