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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Sonata\PageBundle\Page\TemplateManagerInterface;

/**
 * Select a template
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class TemplateChoiceType extends AbstractType
{
    /**
     * @var TemplateManagerInterface
     */
    protected $manager;

    /**
     * @param TemplateManagerInterface $manager
     */
    public function __construct(TemplateManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->getTemplates()
        ));
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        $templates = array();
        foreach ($this->manager->getAll() as $code => $template) {
            $templates[$code] = $template->getName();
        }

        return $templates;
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_page_template';
    }
}
