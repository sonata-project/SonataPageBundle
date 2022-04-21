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
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\SeoBundle\Block\Breadcrumb\BaseBreadcrumbMenuBlockService;
use Twig\Environment;

/**
 * BlockService for homepage breadcrumb.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class BreadcrumbBlockService extends BaseBreadcrumbMenuBlockService implements EditableBlockService
{
    /**
     * @var CmsManagerSelectorInterface
     */
    protected $cmsSelector;

    public function __construct(Environment $templating, FactoryInterface $factory, CmsManagerSelectorInterface $cmsSelector)
    {
        $this->cmsSelector = $cmsSelector;

        parent::__construct($templating, $factory);
    }

    public function getName()
    {
        return 'sonata.page.block.breadcrumb';
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.page.block.breadcrumb', null, null, 'SonataPageBundle', [
            'class' => 'fa fa-bars',
        ]);
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

    public function handleContext(string $context): bool
    {
        // TODO: Implement handleContext() method.
        return 'homepage' === $context;
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        // TODO: Implement configureEditForm() method.
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        // TODO: Implement configureCreateForm() method.
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        // TODO: Implement validate() method.
    }
}
