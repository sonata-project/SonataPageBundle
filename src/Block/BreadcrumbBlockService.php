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

    public function __construct(
        Environment $twig,
        FactoryInterface $factory,
        CmsManagerSelectorInterface $cmsSelector
    ) {
        parent::__construct($twig, $factory);

        $this->cmsSelector = $cmsSelector;
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata($this->getName(), null, null, 'SonataPageBundle', [
            'class' => 'fa fa-bars',
        ]);
    }

    public function handleContext(string $context): bool
    {
        return $this->getName() === $context;
    }

    protected function getMenu(BlockContextInterface $blockContext): ItemInterface
    {
        $blockContext->setSetting('include_homepage_link', false);

        $menu = parent::getMenu($blockContext);

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
     * @return string
     */
    private function getName()
    {
        return 'sonata.page.block.breadcrumb';
    }

    /**
     * @return PageInterface|null
     */
    private function getCurrentPage()
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
