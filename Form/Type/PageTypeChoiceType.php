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

use Sonata\PageBundle\Page\PageServiceManagerInterface;

/**
 * Select a page type
 *
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 */
class PageTypeChoiceType extends AbstractType
{
    /**
     * @var PageServiceManagerInterface
     */
    protected $manager;

    /**
     * @param PageServiceManagerInterface $manager
     */
    public function __construct(PageServiceManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->getPageTypes()
        ));
    }

    /**
     * @return array
     */
    public function getPageTypes()
    {
        $services = $this->manager->getAll();
        $types = array();
        foreach ($services as $id => $service) {
            $types[$id] = $service->getName();
        }

        asort($types);

        return $types;
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
        return 'sonata_page_type_choice';
    }
}
