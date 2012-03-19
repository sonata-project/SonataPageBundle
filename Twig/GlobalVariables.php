<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class GlobalVariables
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getSiteAvailables()
    {
        return $this->container->get('sonata.page.manager.site')->findBy(array(
            'enabled' => true
        ));
    }

    public function getCmsManager()
    {
        return $this->container->get('sonata.page.cms_manager_selector')->retrieve();
    }

    public function getCurrentSite()
    {
        return $this->container->get('sonata.page.site.selector')->retrieve();
    }
}