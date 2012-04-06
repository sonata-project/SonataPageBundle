<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\PageBundle\CmsManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

class CmsManagerSelector implements CmsManagerSelectorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve()
    {
        if ($this->isEditor()) {
            $manager = $this->container->get('sonata.page.cms.page');
        } else {
            $manager = $this->container->get('sonata.page.cms.snapshot');
        }

        return $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function isEditor()
    {
        $securityContext = $this->container->get('security.context');

        return $securityContext->getToken() !== null && $securityContext->isGranted('ROLE_SONATA_PAGE_ADMIN_PAGE_EDIT');
    }
}