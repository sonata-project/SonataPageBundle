<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\Listener;

/**
 * This class redirect the onCoreResponse event to the correct
 * cms manager upon user permission
 */
class ResponseListener
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function onCoreResponse($event)
    {
        $securityContext = $this->container->get('security.context');

        if ($securityContext->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT')) {
            $manager = $this->container->get('sonata.page.cms.page');
        } else {
            $manager = $this->container->get('sonata.page.cms.snapshot');
        }

        return $manager->onCoreResponse($event);
    }
}