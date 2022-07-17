<?php

declare(strict_types=1);

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
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\SeoBundle\Block\Breadcrumb\BaseBreadcrumbMenuBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * BlockService for homepage breadcrumb.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
final class BreadcrumbBlockService extends BaseBreadcrumbMenuBlockService
{
    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    /**
     * @param string $context
     * @param string $name
     */
    public function __construct($context, $name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory, CmsManagerSelectorInterface $cmsSelector)
    {
        $this->cmsSelector = $cmsSelector;

        parent::__construct($context, $name, $templating, $menuProvider, $factory);
    }

    public function getName()
    {
        return 'sonata.page.block.breadcrumb';
    }

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataPageBundle', [
            'class' => 'fa fa-bars',
        ]);
    }

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

            $this->createMenuItem($menu, $parent);
        }

        if (!$page->isError()) {
            $this->createMenuItem($menu, $page);
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

    private function createMenuItem(ItemInterface $menu, PageInterface $page): void
    {
        $label = $page->getTitle();
        $extras = [];

        if (null === $label) {
            $label = $page->getName();

            $extras['translation_domain'] = 'SonataPageBundle';
        }

        $menu->addChild($label, [
            'route' => 'page_slug',
            'routeParameters' => [
                'path' => $page->getUrl(),
            ],
            'extras' => $extras,
        ]);
    }
}
