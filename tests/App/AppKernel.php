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
use FOS\RestBundle\FOSRestBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\BlockBundle\SonataBlockBundle;
use Sonata\CacheBundle\SonataCacheBundle;
use Sonata\CoreBundle\SonataCoreBundle;
use Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle;
use Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle;
use Sonata\Form\Bridge\Symfony\SonataFormBundle;
use Sonata\NotificationBundle\SonataNotificationBundle;
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
use Symfony\Component\Routing\RouteCollectionBuilder;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', false);
    }

    public function registerBundles(): array
    {
        $bundles = [
            new FrameworkBundle(),
            new FOSRestBundle(),
            new TwigBundle(),
            new SecurityBundle(),
            new KnpMenuBundle(),
            new SonataBlockBundle(),
            new SonataDoctrineBundle(),
            new SonataAdminBundle(),
            new CmfRoutingBundle(),
            new JMSSerializerBundle(),
            new DoctrineBundle(),
            new SonataNotificationBundle(),
            new SonataDoctrineORMAdminBundle(),
            new SonataSeoBundle(),
            new SonataCacheBundle(),
            new SonataPageBundle(),
            new SonataFormBundle(),
            new SonataTwigBundle(),
            new NelmioApiDocBundle(),
        ];

        if (class_exists(SonataCoreBundle::class)) {
            $bundles[] = new SonataCoreBundle();
        }

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

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $routes->import($this->getProjectDir().'/Resources/config/routing/routes.yaml');

        if (class_exists(Operation::class)) {
            $routes->import(__DIR__.'/Resources/config/routing/api_nelmio_v3.yml', '/', 'yaml');
        } else {
            $routes->import(__DIR__.'/Resources/config/routing/api_nelmio_v2.yml', '/', 'yaml');
        }
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir().'/Resources/config/config.yaml');
    }

    private function getBaseDir(): string
    {
        return sys_get_temp_dir().'/sonata-page-bundle/var/';
    }
}
