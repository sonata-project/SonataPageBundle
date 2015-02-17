<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Sonata\PageBundle\DependencyInjection\Compiler\CacheCompilerPass;
use Sonata\PageBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Sonata\PageBundle\DependencyInjection\Compiler\PageServiceCompilerPass;

/**
 * SonataPageBundle
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataPageBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CacheCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
        $container->addCompilerPass(new PageServiceCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $container = $this->container;
        $class     = $this->container->getParameter('sonata.page.page.class');

        call_user_func(array($class, 'setSlugifyMethod'), function($text) use ($container) {
            if ($container->hasParameter('sonata.page.slugify_service')) {
                $id = $container->getParameter('sonata.page.slugify_service');
            } else {
                $id = 'sonata.core.slugify.native'; // default BC value, you should use sonata.core.slugify.cocur
            }

            $service = $container->get($id);

            return $service->slugify($text);
        });
    }
}
