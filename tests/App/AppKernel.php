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

namespace Sonata\PageBundle\Tests\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\BlockBundle\Cache\HttpCacheHandler;
use Sonata\BlockBundle\SonataBlockBundle;
use Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle;
use Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle;
use Sonata\Form\Bridge\Symfony\SonataFormBundle;
use Sonata\PageBundle\SonataPageBundle;
use Sonata\SeoBundle\SonataSeoBundle;
use Sonata\Twig\Bridge\Symfony\SonataTwigBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', false);
    }

    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new TwigBundle(),
            new SecurityBundle(),
            new KnpMenuBundle(),
            new SonataBlockBundle(),
            new SonataDoctrineBundle(),
            new SonataAdminBundle(),
            new CmfRoutingBundle(),
            new DoctrineBundle(),
            new SonataDoctrineORMAdminBundle(),
            new SonataSeoBundle(),
            new SonataPageBundle(),
            new SonataFormBundle(),
            new SonataTwigBundle(),
        ];

        return $bundles;
    }

    public function getCacheDir(): string
    {
        return $this->getBaseDir().'cache';
    }

    public function getLogDir(): string
    {
        return $this->getBaseDir().'log';
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    /**
     * TODO: add typehint when support for Symfony < 5.1 is dropped.
     *
     * @param RoutingConfigurator $routes
     */
    protected function configureRoutes($routes): void
    {
        $routes->import($this->getProjectDir().'/config/routes.yaml');
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/config/config.yaml');

        if (class_exists(AuthenticatorManager::class)) {
            $loader->load($this->getProjectDir().'/config/config_symfony_v5.yaml');
        } else {
            $loader->load($this->getProjectDir().'/config/config_symfony_v4.yaml');
        }

        if (class_exists(HttpCacheHandler::class)) {
            $loader->load($this->getProjectDir().'/config/config_sonata_block_v4.yaml');
        }
    }

    private function getBaseDir(): string
    {
        return sys_get_temp_dir().'/sonata-page-bundle/var/';
    }
}
