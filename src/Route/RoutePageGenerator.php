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

namespace Sonata\PageBundle\Route;

use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Listener\ExceptionListener;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author Vincent Composieux <vincent.composieux@gmail.com>
 */
final class RoutePageGenerator
{
    public function __construct(
        private RouterInterface $router,
        private PageManagerInterface $pageManager,
        private DecoratorStrategyInterface $decoratorStrategy,
        private ExceptionListener $exceptionListener
    ) {
    }

    public function update(SiteInterface $site, ?OutputInterface $output = null, bool $clean = false): void
    {
        $message = sprintf(
            ' > <info>Updating core routes for site</info> : <comment>%s - %s</comment>',
            $site->getName() ?? '',
            $site->getUrl() ?? ''
        );

        $this->writeln($output, [
            str_repeat('=', \strlen($message)),
            '',
            $message,
            '',
            str_repeat('=', \strlen($message)),
        ]);

        $knowRoutes = [];

        $root = $this->pageManager->getPageByUrl($site, '/');

        // no root url for the given website, create one
        if (null === $root) {
            $root = $this->createRootPage($site);
            $this->pageManager->save($root);
        }

        // Iterate over declared routes from the routing mechanism
        foreach ($this->router->getRouteCollection()->all() as $name => $route) {
            $name = trim($name);
            $displayName = $this->displayName($name);

            $knowRoutes[] = $name;

            $page = $this->pageManager->findOneBy([
                'routeName' => $name,
                'site' => $site->getId(),
            ]);

            $routeHostRegex = $route->compile()->getHostRegex();

            if (
                !$this->decoratorStrategy->isRouteNameDecorable($name)
                || !$this->decoratorStrategy->isRouteUriDecorable($route->getPath())
                || (null !== $routeHostRegex
                && 0 === preg_match($routeHostRegex, $site->getHost() ?? ''))
            ) {
                if (null !== $page) {
                    $page->setEnabled(false);

                    $this->writeln($output, sprintf(
                        '  <error>DISABLE</error> <error>% -50s</error> %s',
                        $name,
                        $route->getPath()
                    ));
                } else {
                    continue;
                }
            }

            $update = true;
            $requirements = $route->getRequirements();

            if (null === $page) {
                $update = false;

                $page = $this->pageManager->createWithDefaults([
                    'routeName' => $name,
                    'name' => $displayName,
                    'url' => $route->getPath(),
                    'site' => $site,
                    'requestMethod' => $requirements['_method'] ?? 'GET|POST|HEAD|DELETE|PUT',
                ]);
            }

            if (null === $page->getParent() && $page->getId() !== $root->getId()) {
                $page->setParent($root);
            }

            $page->setSlug($route->getPath());
            $page->setUrl($route->getPath());
            $page->setRequestMethod($requirements['_method'] ?? 'GET|POST|HEAD|DELETE|PUT');

            $this->pageManager->save($page);

            $this->writeln($output, sprintf(
                '  <info>%s</info> % -50s %s',
                $update ? 'UPDATE ' : 'CREATE ',
                $name,
                $route->getPath()
            ));
        }

        // Iterate over error pages
        foreach ($this->exceptionListener->getHttpErrorCodes() as $name) {
            $name = trim($name);
            $displayName = $this->displayName($name);

            $knowRoutes[] = $name;

            $page = $this->pageManager->findOneBy([
                'routeName' => $name,
                'site' => $site->getId(),
            ]);

            if (null === $page) {
                $page = $this->pageManager->createWithDefaults([
                    'routeName' => $name,
                    'name' => $displayName,
                    'decorate' => false,
                    'site' => $site,
                ]);

                $this->writeln($output, sprintf('  <info>%s</info> % -50s %s', 'CREATE ', $name, ''));
            }

            // an internal page or an error page should not have any parent (no direct access)
            $page->setParent(null);
            $this->pageManager->save($page);
        }

        $has = false;

        foreach ($this->pageManager->getHybridPages($site) as $page) {
            if (!$page->isHybrid() || $page->isInternal()) {
                continue;
            }

            if (!\in_array($page->getRouteName(), $knowRoutes, true)) {
                if (!$has) {
                    $has = true;

                    $this->writeln($output, ['', 'Some hybrid pages does not exist anymore', str_repeat('-', 80)]);
                }

                if ($clean) {
                    $this->pageManager->delete($page);

                    $this->writeln($output, sprintf('  <error>REMOVED</error>   %s', $page->getRouteName() ?? ''));
                } else {
                    $this->writeln($output, sprintf('  <error>ERROR</error>   %s', $page->getRouteName() ?? ''));
                }
            }
        }

        if ($has && !$clean) {
            $this->writeln(
                $output,
                <<<'MSG'
                    <error>
                      *WARNING* : Pages has been updated however some pages do not exist anymore.
                                  You must remove them manually.
                    </error>
                    MSG
            );
        }
    }

    /**
     * @param string|iterable<string> $message A string message to output
     */
    private function writeln(?OutputInterface $output, string|iterable $message): void
    {
        if (null !== $output) {
            $output->writeln($message);
        }
    }

    /**
     * Generate root page (parent for another generated page).
     * If root path available in config - create from config
     * Else - generate it statically.
     */
    private function createRootPage(SiteInterface $site): PageInterface
    {
        foreach ($this->router->getRouteCollection()->all() as $name => $route) {
            if ('/' === $route->getPath()) {
                $requirements = $route->getRequirements();
                $name = trim($name);
                $displayName = $this->displayName($name);

                return $this->pageManager->createWithDefaults([
                    'routeName' => $name,
                    'name' => $displayName,
                    'url' => $route->getPath(),
                    'site' => $site,
                    'requestMethod' => $requirements['_method'] ?? 'GET|POST|HEAD|DELETE|PUT',
                    'slug' => '/',
                ]);
            }
        }

        return $this->pageManager->createWithDefaults([
            'routeName' => PageInterface::PAGE_ROUTE_CMS_NAME,
            'name' => 'Homepage',
            'url' => '/',
            'site' => $site,
            'requestMethod' => 'GET|POST|HEAD|DELETE|PUT',
            'slug' => '/',
        ]);
    }

    private function displayName(string $name): string
    {
        return ucwords(trim(str_replace('_', ' ', $name)));
    }
}
