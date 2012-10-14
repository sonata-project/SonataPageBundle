<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\CmsManager;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Templating\StreamingEngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Sonata\SeoBundle\Seo\SeoPageInterface;

/**
 * Render a PageInterface
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PageRenderer implements PageRendererInterface
{
    protected $router;

    protected $cmsSelector;

    protected $templating;

    protected $templates = array();

    protected $defaultTemplateCode = 'default';

    protected $defaultTemplatePath = 'SonataPageBundle::layout.html.twig';

    protected $errorCodes;

    protected $seoPage;

    /**
     * @param RouterInterface             $router
     * @param CmsManagerSelectorInterface $cmsSelector
     * @param EngineInterface             $templating
     * @param array                       $templates
     * @param array                       $errorCodes
     * @param SeoPageInterface            $seoPage
     */
    public function __construct(RouterInterface $router, CmsManagerSelectorInterface $cmsSelector, EngineInterface $templating, array $templates, array $errorCodes, SeoPageInterface $seoPage = null)
    {
        $this->router      = $router;
        $this->cmsSelector = $cmsSelector;
        $this->templating  = $templating;
        $this->templates   = $templates;
        $this->errorCodes  = $errorCodes;
        $this->seoPage     = $seoPage;
    }

    /**
     * {@inheritdoc}
     */
    public function render(PageInterface $page, array $params = array(), Response $response = null)
    {
        $cms = $this->cmsSelector->retrieve();

        if (!$response) {

            if ($page->getTarget()) {
                $page->addHeader('Location', sprintf('%s%s', $this->router->getContext()->getBaseUrl(), $page->getTarget()->getUrl()));

                return new Response('', 302, $page->getHeaders());
            }

            if ($page->getHeaders()) {
                $response = new Response('', 200, $page->getHeaders());
            }
        }

        $template = false;
        if ($cms->getCurrentPage()) {
            $template = $this->getTemplate($cms->getCurrentPage()->getTemplateCode())->getPath();
        }

        if (!$template) {
            $template = $this->defaultTemplatePath;
        }

        $params['page']        = $page;
        $params['site']        = $page->getSite();
        $params['manager']     = $cms;
        $params['error_codes'] = $this->errorCodes;

        $this->addSeoMeta($page);

        if ($this->templating instanceof StreamingEngineInterface) {
            $templating = $this->templating;

            return new StreamedResponse(
                function() use ($templating, $template, &$params) { $templating->stream($template, $params); },
                $response ? $response->getStatusCode() : 200,
                $response ? $response->headers->all() : array()
            );
        }

        $response = $this->templating->renderResponse($template, $params, $response);

        if (!$this->cmsSelector->isEditor() && $page->isCms()) {
            $response->setTtl($page->getTtl());
        }

        return $response;
    }

    /**
     * @param PageInterface $page
     *
     * @return void
     */
    protected function addSeoMeta(PageInterface $page)
    {
        if (!$this->seoPage) {
            return;
        }

        $this->seoPage->setTitle($page->getTitle() ?: $page->getName());

        if ($page->getMetaDescription()) {
            $this->seoPage->addMeta('name', 'description', $page->getMetaDescription());
        }

        if ($page->getMetaKeyword()) {
            $this->seoPage->addMeta('name', 'keywords', $page->getMetaKeyword());
        }

        $this->seoPage->addMeta('property', 'og:type', 'article');
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTemplateCode()
    {
        return $this->defaultTemplateCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultTemplateCode($code)
    {
        $this->defaultTemplateCode = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($code)
    {
        if (!isset($this->templates[$code])) {
            throw new \RunTimeException(sprintf('No template references whith the code : %s', $code));
        }

        return $this->templates[$code];
    }
}