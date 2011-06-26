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
use Sonata\PageBundle\DependencyInjection\AddBlockServicePass;
use Sonata\PageBundle\Twig\TwigTemplate;

class SonataPageBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddBlockServicePass());
    }

    public function boot()
    {
        $recorder = $this->container->get('sonata.page.cache.recorder');

        TwigTemplate::attachRecorder($recorder);
    }
}
