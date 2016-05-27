<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Twig;

use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * GlobalVariables.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class GlobalVariables
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
     * @return SiteInterface[]
     */
    public function getSiteAvailables()
    {
        return $this->container->get('sonata.page.manager.site')->findBy([
            'enabled' => true,
        ]);
    }

    /**
     * @return CmsManagerInterface
     */
    public function getCmsManager()
    {
        return $this->container->get('sonata.page.cms_manager_selector')->retrieve();
    }

    /**
     * @return SiteInterface
     */
    public function getCurrentSite()
    {
        return $this->container->get('sonata.page.site.selector')->retrieve();
    }

    /**
     * @return bool
     */
    public function isEditor()
    {
        return $this->container->get('sonata.page.cms_manager_selector')->isEditor();
    }

    /**
     * @return string
     */
    public function getDefaultTemplate()
    {
        $templateManager = $this->container->get('sonata.page.template_manager');

        return $templateManager->get($templateManager->getDefaultTemplateCode())->getPath();
    }

    /**
     * @return array
     */
    public function getAssets()
    {
        return $this->container->getParameter('sonata.page.assets');
    }

    /**
     * @return bool
     */
    public function isInlineEditionOn()
    {
        return $this->container->getParameter('sonata.page.is_inline_edition_on');
    }
}
