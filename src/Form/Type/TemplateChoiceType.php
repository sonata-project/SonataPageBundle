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

namespace Sonata\PageBundle\Form\Type;

use Sonata\PageBundle\Page\TemplateManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Select a template.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TemplateChoiceType extends AbstractType
{
    /**
     * @var TemplateManagerInterface
     */
    protected $manager;

    public function __construct(TemplateManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaults = [
            'choices' => $this->getTemplates(),
            'choice_translation_domain' => false,
        ];

        // NEXT_MAJOR: Remove (when requirement of Symfony is >= 3.0)
        if (method_exists(FormTypeInterface::class, 'setDefaultOptions')) {
            $defaults['choices_as_values'] = true;
        }

        $resolver->setDefaults($defaults);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver): void
    {
        $this->configureOptions($resolver);
    }

    /**
     * @return string[]
     */
    public function getTemplates()
    {
        $templates = [];
        foreach ($this->manager->getAll() as $code => $template) {
            $templates[$template->getName()] = $code;
        }

        return $templates;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix()
    {
        return 'sonata_page_template';
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
