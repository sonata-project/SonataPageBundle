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
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class PageRenderer implements PageRendererInterface
{
    protected $router;

    protected $cmsSelector;

    protected $templating;

    protected $templates = array();

    protected $defaultTemplateCode = 'default';

    protected $defaultTemplatePath = 'SonataPageBundle::layout.html.twig';

    protected $errorCodes;

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param CmsManagerSelectorInterface $cmsSelector
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     * @param array $templates
     * @param array $errorCodes
     */
    public function __construct(RouterInterface $router, CmsManagerSelectorInterface $cmsSelector, EngineInterface $templating, array $templates, array $errorCodes)
    {
        $this->router      = $router;
        $this->cmsSelector = $cmsSelector;
        $this->templating  = $templating;
        $this->templates   = $templates;
        $this->errorCodes  = $errorCodes;
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

        $params['page']    = $page;
        $params['site']    = $page->getSite();
        $params['manager'] = $cms;
        $params['error_codes'] = $this->errorCodes;

        return $this->templating->renderResponse($template, $params, $response);
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