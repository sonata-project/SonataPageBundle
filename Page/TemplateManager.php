<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Page;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\StreamingEngineInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Sonata\PageBundle\Model\Template;

/**
 * Templates management and rendering
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
class TemplateManager implements TemplateManagerInterface
{
    /**
     * Templating engine
     *
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var array
     */
    protected $defaultParameters;

    /**
     * Collection of available templates
     *
     * @var Template[]
     */
    protected $templates;

    /**
     * Default template code
     *
     * @var string
     */
    protected $defaultTemplateCode = 'default';

    /**
     * Default template path
     *
     * @var string
     */
    protected $defaultTemplatePath = 'SonataPageBundle::layout.html.twig';

    /**
     * Constructor
     *
     * @param EngineInterface $engine            Templating engine
     * @param array           $defaultParameters An array of default view parameters
     */
    public function __construct(EngineInterface $engine, array $defaultParameters = array())
    {
        $this->engine            = $engine;
        $this->defaultParameters = $defaultParameters;
    }

    /**
     * Adds a template
     *
     * @param string   $code     Code
     * @param Template $template Template object
     */
    public function add($code, Template $template)
    {
        $this->templates[$code] = $template;
    }

    /**
     * Returns the template by code
     *
     * @param string $code
     *
     * @return Template|null
     */
    public function get($code)
    {
        if (!isset($this->templates[$code])) {
            return null;
        }

        return $this->templates[$code];
    }

    /**
     * Sets the default template code
     *
     * @param string $code
     */
    public function setDefaultTemplateCode($code)
    {
        $this->defaultTemplateCode = $code;
    }

    /**
     * Returns the default template code
     *
     * @return string
     */
    public function getDefaultTemplateCode()
    {
        return $this->defaultTemplateCode;
    }

    /**
     * Sets the templates
     *
     * @param Template[] $templates
     */
    public function setAll($templates)
    {
        $this->templates = $templates;
    }

    /**
     * Returns the templates
     *
     * @return Template[]
     */
    public function getAll()
    {
        return $this->templates;
    }

    /**
     * Renders a template code
     *
     * @param string   $code       Template code
     * @param array    $parameters An array of view parameters
     * @param Response $response   Response to update
     *
     * @return Response
     */
    public function renderResponse($code, array $parameters = array(), Response $response = null)
    {
        return $this->engine->renderResponse(
            $this->getTemplatePath($code),
            array_merge($this->defaultParameters, $parameters),
            $response
        );
    }

    /**
     * Returns the template path for given code
     *
     * @param string|null $code
     *
     * @return string
     */
    protected function getTemplatePath($code)
    {
        $code = $code ?: $this->getDefaultTemplateCode();
        $template = $this->get($code);

        return $template ? $template->getPath() : $this->defaultTemplatePath;
    }
}
