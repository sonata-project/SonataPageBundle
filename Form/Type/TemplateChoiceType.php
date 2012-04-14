<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Sonata\PageBundle\CmsManager\PageRendererInterface;

class TemplateChoiceType extends ChoiceType
{
    protected $renderer;

    public function __construct(PageRendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getDefaultOptions()
    {
        $options = parent::getDefaultOptions();

        $options['choices'] = $this->getTemplates();

        return $options;
    }

    public function getTemplates()
    {
        $templates = array();
        foreach ($this->renderer->getTemplates() as $code => $template) {
            $templates[$code] = $template->getName();
        }

        return $templates;
    }
}