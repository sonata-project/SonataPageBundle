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
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\BlockServiceInterface;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\SeoBundle\Block\Breadcrumb\BaseBreadcrumbMenuBlockService;
use Twig\Environment;

/**
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
final class BreadcrumbBlockService extends BaseBreadcrumbMenuBlockService
{
    private CmsManagerSelectorInterface $cmsSelector;

    /**
     * @param BlockServiceInterface&EditableBlockService $menuBlock
     */
    public function __construct(
        Environment $twig,
        object $menuBlock,
        FactoryInterface $factory,
        CmsManagerSelectorInterface $cmsSelector
    ) {
        parent::__construct($twig, $menuBlock, $factory);

        $this->cmsSelector = $cmsSelector;
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.page.block.breadcrumb', null, null, 'SonataPageBundle', [
            'class' => 'fa fa-bars',
        ]);
    }

    public function handleContext(string $context): bool
    {
        return 'page' === $context;
    }

    protected function getMenu(BlockContextInterface $blockContext): ItemInterface
    {
        $blockContext->setSetting('include_homepage_link', false);

        $menu = parent::getMenu($blockContext);

        $page = $this->getCurrentPage();

        if (null === $page) {
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

    private function getCurrentPage(): ?PageInterface
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

        $menu->addChild($label ?? '', [
            'route' => 'page_slug',
            'routeParameters' => [
                'path' => $page->getUrl(),
            ],
            'extras' => $extras,
        ]);
    }
}
