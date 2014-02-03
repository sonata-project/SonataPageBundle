<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Block;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\SeoBundle\Block\Breadcrumb\BaseBreadcrumbMenuBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * BlockService for homepage breadcrumb.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class BreadcrumbBlockService extends BaseBreadcrumbMenuBlockService
{
    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    /**
     * @param string                      $context
     * @param string                      $name
     * @param EngineInterface             $templating
     * @param MenuProviderInterface       $menuProvider
     * @param FactoryInterface            $factory
     * @param CmsManagerSelectorInterface $cmsSelector
     */
    public function __construct($context, $name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory, CmsManagerSelectorInterface $cmsSelector)
    {
        $this->cmsSelector = $cmsSelector;

        parent::__construct($context, $name, $templating, $menuProvider, $factory);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata.page.block.breadcrumb';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $blockContext->setSetting('include_homepage_link', false);

        $menu = $this->getRootMenu($blockContext);

        $page = $this->getCurrentPage();

        if (!$page) {
            return $menu;
        }

        $parents = $page->getParents();

        foreach ($parents as $parent) {
            if ($parent->isError()) {
                continue;
            }

            $menu->addChild($parent->getName(), array(
                'route'           => 'page_slug',
                'routeParameters' => array(
                    'path' => $parent->getUrl(),
                ),
            ));
        }

        if (!$page->isError()) {
            $menu->addChild($page->getName(), array(
                'route'           => 'page_slug',
                'routeParameters' => array(
                    'path' => $page->getUrl(),
                ),
            ));
        }

        return $menu;
    }

    /**
     * Return the current Page.
     *
     * @return \Sonata\PageBundle\Model\PageInterface
     */
    protected function getCurrentPage()
    {
        $cms  = $this->cmsSelector->retrieve();

        return $cms->getCurrentPage();
    }
}
