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

    public function __construct(
        string $name,
        TemplateManagerInterface $templateManager,
        ?SeoPageInterface $seoPage = null
    ) {
        parent::__construct($name);

        $this->templateManager = $templateManager;
        $this->seoPage = $seoPage;
    }

    public function execute(
        PageInterface $page,
        Request $request,
        array $parameters = [],
        ?Response $response = null
    ): Response {
        $this->updateSeoPage($page);

        $templateCode = $page->getTemplateCode();

        if (null === $templateCode) {
            throw new \RuntimeException('The page template is not defined');
        }

        return $this->templateManager->renderResponse($templateCode, $parameters, $response);
    }

    private function updateSeoPage(PageInterface $page): void
    {
        if (null === $this->seoPage) {
            return;
        }

        /*
         * Always prefer the page title, if set.
         * Do not use the (internal) page name as a fallback
         */
        $title = $page->getTitle();
        if (null !== $title) {
            $this->seoPage->setTitle($title);
        }

        $metaDescription = $page->getMetaDescription();
        if (null !== $metaDescription) {
            $this->seoPage->addMeta('name', 'description', $metaDescription);
        }

        $metaKeywords = $page->getMetaKeyword();
        if (null !== $metaKeywords) {
            $this->seoPage->addMeta('name', 'keywords', $metaKeywords);
        }

        $this->seoPage->addMeta('property', 'og:type', 'article');
        $this->seoPage->addHtmlAttributes('prefix', 'og: http://ogp.me/ns#');
    }
}
