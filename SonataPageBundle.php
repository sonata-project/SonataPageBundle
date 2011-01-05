<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\PageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SonataPageBundle extends Bundle
{

    public function boot()
    {

        $this->container->get('event_dispatcher')->connect('core.response', array(
            $this->container->get('page.manager'),
            'filterReponse'
        ), -1); // tweak the priority to symfony profiler and error can work on layout
    }
}