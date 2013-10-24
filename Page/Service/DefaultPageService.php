<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Page\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sonata\SeoBundle\Seo\SeoPageInterface;

use Sonata\PageBundle\Page\Service\BasePageService;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\TemplateManagerInterface;

/**
 * Default page service to render a page template.
 *
 * Note: this service is backward-compatible and functions like the old page renderer class.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
class DefaultPageService extends BasePageService
{
    /**
     * @var TemplateManagerInterface
     */
    protected $templateManager;

    /**
     * @var SeoPageInterface
     */
    protected $seoPage;

    /**
     * Constructor
     *
     * @param string                    $name            Page service name
     * @param TemplateManagerInterface  $templateManager Template manager
     * @param SeoPageInterface          $seoPage         SEO page object
     */
    public function __construct($name, TemplateManagerInterface $templateManager, SeoPageInterface $seoPage = null)
    {
        $this->name            = $name;
        $this->templateManager = $templateManager;
        $this->seoPage         = $seoPage;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(PageInterface $page, Request $request, array $parameters = array(), Response $response = null)
    {
        $this->updateSeoPage($page);

        $response = $this->templateManager->renderResponse($page->getTemplateCode(), $parameters, $response);

        return $response;
    }

    /**
     * Updates the SEO page values for given page instance
     *
     * @param PageInterface $page
     */
    protected function updateSeoPage(PageInterface $page)
    {
        if (!$this->seoPage) {
            return;
        }

        if ($page->getTitle()) {
            $this->seoPage->setTitle($page->getTitle() ?: $page->getName());
        }

        if ($page->getMetaDescription()) {
            $this->seoPage->addMeta('name', 'description', $page->getMetaDescription());
        }

        if ($page->getMetaKeyword()) {
            $this->seoPage->addMeta('name', 'keywords', $page->getMetaKeyword());
        }

        $this->seoPage->addMeta('property', 'og:type', 'article');
        $this->seoPage->addHtmlAttributes('prefix', 'og: http://ogp.me/ns#');
    }
}
