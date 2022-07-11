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

namespace Sonata\PageBundle\Page\Service;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Page\TemplateManagerInterface;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default page service to render a page template.
 *
 * Note: this service is backward-compatible and functions like the old page renderer class.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 *
 * @final since sonata-project/page-bundle 3.26
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
     * @param string                   $name            Page service name
     * @param TemplateManagerInterface $templateManager Template manager
     * @param SeoPageInterface         $seoPage         SEO page object
     */
    public function __construct($name, TemplateManagerInterface $templateManager, ?SeoPageInterface $seoPage = null)
    {
        parent::__construct($name);

        $this->templateManager = $templateManager;
        $this->seoPage = $seoPage;
    }

    public function execute(PageInterface $page, Request $request, array $parameters = [], ?Response $response = null)
    {
        $this->updateSeoPage($page);

        return $this->templateManager->renderResponse($page->getTemplateCode(), $parameters, $response);
    }

    /**
     * Updates the SEO page values for given page instance.
     */
    protected function updateSeoPage(PageInterface $page): void
    {
        if (!$this->seoPage) {
            return;
        }

        /*
         * Always prefer the page title, if set.
         * Do not use the (internal) page name as a fallback
         */
        if ($page->getTitle()) {
            $this->seoPage->setTitle($page->getTitle());
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
