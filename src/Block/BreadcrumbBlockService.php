<?php

/*
 * This file is part of the Sonata Project package.
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
use Sonata\BlockBundle\Menu\MenuRegistryInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageInterface;
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
     * @param MenuRegistryInterface|array $menuRegistry
     *
     * NEXT_MAJOR: Use MenuRegistryInterface as a type of $menuRegistry argument
     */
    public function __construct($context, $name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory, CmsManagerSelectorInterface $cmsSelector, $menuRegistry = [])
    {
        $this->cmsSelector = $cmsSelector;

        /*
         * NEXT_MAJOR: Remove if statements
         */
        if (!$menuRegistry instanceof MenuRegistryInterface && !is_array($menuRegistry)) {
            throw new \InvalidArgumentException(sprintf(
                'MenuRegistry must be either type of array or instance of %s',
                MenuRegistryInterface::class
            ));
        } elseif (is_array($menuRegistry)) {
            @trigger_error(sprintf(
                'Initializing %s without menuRegistry parameter is deprecated since 3.x and will'.
                ' be removed in 4.0. Use an instance of %s as last argument.',
                __CLASS__,
                MenuRegistryInterface::class
            ), E_USER_DEPRECATED);
        }

        parent::__construct($context, $name, $templating, $menuProvider, $factory, $menuRegistry);
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

            $menu->addChild($parent->getName(), [
                'route' => 'page_slug',
                'routeParameters' => [
                    'path' => $parent->getUrl(),
                ],
            ]);
        }

        if (!$page->isError()) {
            $menu->addChild($page->getName(), [
                'route' => 'page_slug',
                'routeParameters' => [
                    'path' => $page->getUrl(),
                ],
            ]);
        }

        return $menu;
    }

    /**
     * Return the current Page.
     *
     * @return PageInterface
     */
    protected function getCurrentPage()
    {
        $cms = $this->cmsSelector->retrieve();

        return $cms->getCurrentPage();
    }
}
