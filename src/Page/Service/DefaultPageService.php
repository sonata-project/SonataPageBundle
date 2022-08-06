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
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class DefaultPageService extends BasePageService
{
    private TemplateManagerInterface $templateManager;

    private ?SeoPageInterface $seoPage;

    /**
     * @param string                   $name            Page service name
     * @param TemplateManagerInterface $templateManager Template manager
     * @param SeoPageInterface|null    $seoPage         SEO page object
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

        $templateCode = $page->getTemplateCode();

        if (null === $templateCode) {
            throw new \RuntimeException('The page template is not defined');
        }

        return $this->templateManager->renderResponse($templateCode, $parameters, $response);
    }

    private function updateSeoPage(PageInterface $page): void
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
