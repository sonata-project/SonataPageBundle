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

namespace Sonata\PageBundle\Page;

use Sonata\PageBundle\Model\Template;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Templates management and rendering.
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
final class TemplateManager implements TemplateManagerInterface
{
    /**
     * Templating twig.
     */
    private Environment $twig;

    private array $defaultParameters;

    /**
     * Collection of available templates.
     */
    private array $templates = [];

    /**
     * Default template code.
     */
    private string $defaultTemplateCode = 'default';

    /**
     * Default template path.
     */
    private string $defaultTemplatePath = '@SonataPage/layout.html.twig';

    /**
     * @param Environment $twig              Templating twig
     * @param array       $defaultParameters An array of default view parameters
     */
    public function __construct(Environment $twig, array $defaultParameters = [])
    {
        $this->twig = $twig;
        $this->defaultParameters = $defaultParameters;
    }

    public function add($code, Template $template): void
    {
        $this->templates[$code] = $template;
    }

    public function get($code)
    {
        if (!isset($this->templates[$code])) {
            return null;
        }

        return $this->templates[$code];
    }

    public function setDefaultTemplateCode($code): void
    {
        $this->defaultTemplateCode = $code;
    }

    public function getDefaultTemplateCode()
    {
        return $this->defaultTemplateCode;
    }

    public function setAll($templates): void
    {
        $this->templates = $templates;
    }

    public function getAll()
    {
        return $this->templates;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function renderResponse(string $code, array $parameters = [], ?Response $response = null): Response
    {
        $response ??= new Response();

        return $response->setContent(
            $this->twig->render(
                $this->getTemplatePath($code),
                array_merge($this->defaultParameters, $parameters),
            )
        );
    }

    /**
     * Returns the template path for given code.
     */
    private function getTemplatePath(?string $code): string
    {
        $code = $code ?: $this->getDefaultTemplateCode();
        $template = $this->get($code);

        return $template ? $template->getPath() : $this->defaultTemplatePath;
    }
}
