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
    
class SonataPageBundle extends Bundle
{
    
    public function boot()
    {
        // lazy load the manager to avoid :
        //      You cannot create a service ("sonata.page.manager") of an inactive scope ("request").
        $container = $this->container;
        $function = function($event, $response) use($container) {
            return $container->get('sonata.page.manager')->filterReponse($event, $response);
        };
        $this->container->get('event_dispatcher')->connect('core.response', $function, -1);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddBlockServicePass());
    }
}
