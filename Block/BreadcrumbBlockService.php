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
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\SeoBundle\Block\Breadcrumb\BaseBreadcrumbMenuBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ContainerInterface
     */
    protected $defaultLocale;

    /**
     * @param string                      $context
     * @param string                      $name
     * @param EngineInterface             $templating
     * @param MenuProviderInterface       $menuProvider
     * @param FactoryInterface            $factory
     * @param CmsManagerSelectorInterface $cmsSelector
     */
    public function __construct($context, $name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory, CmsManagerSelectorInterface $cmsSelector, RequestStack $requestStack, $defaultLocale)
    {
        $this->cmsSelector = $cmsSelector;
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;

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
                'route' => 'page_slug',
                'routeParameters' => array(
                    'path' => $this->getLocatedUrl($parent),
                ),
            ));
        }

        if (!$page->isError()) {
            $menu->addChild($page->getName(), array(
                'route' => 'page_slug',
                'routeParameters' => array(
                    'path' => $this->getLocatedUrl($page),
                ),
            ));
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

    /**
     * Return the current Locale.
     *
     * @return string
     */
    protected function getLocale()
    {
        $locale = $this->requestStack->getRequest()->getLocale();
        if ($locale == null) {
            $locale = $this->getContainer()->getParameter('locale');
        }

        return $locale;
    }

    /**
     * Return the Page Url with locale set.
     *
     * @return string
     */
    protected function getLocatedUrl(PageInterface $page)
    {
        $url = $page->getUrl();
        $locale = $this->getLocale();
        if (strpos($url, '{_locale}') and $locale != null) {
            $url = str_replace('{_locale}', $locale, $url);
        }

        return $url;
    }

    /**
     * Return the Container.
     *
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }
}
