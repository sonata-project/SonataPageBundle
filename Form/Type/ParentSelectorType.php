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

use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\PageInterface;

use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;

class ParentSelectorType extends ModelType
{
    protected $manager;

    public function __construct(PageManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'template'          => 'choice',
            'multiple'          => false,
            'expanded'          => false,
            'model_manager'     => null,
            'class'             => null,
            'property'          => null,
            'query'             => null,
            'choices'           => $this->getParentChoices(isset($options['page']) ? $options['page'] : null),
            'parent'            => 'choice',
            'preferred_choices' => array(),
            'page'              => null,
        );

        $options = array_replace($defaultOptions, $options);

        if (!isset($options['choice_list'])) {
            $defaultOptions['choice_list'] = new ModelChoiceList(
                $options['model_manager'],
                $options['class'],
                $options['property'],
                $options['query'],
                $options['choices']
            );
        }

        return $defaultOptions;
    }

    public function getParentChoices(PageInterface $currentPage = null)
    {
        $pages = $this->manager->loadPages();

        $roots = array();

        foreach ($pages as $page) {
            if ($page->getParent() || ($currentPage && $currentPage->getId() == $page->getId())) {
                continue;
            }

            if ($page->isDynamic()) {
                continue;
            }

            $roots[$page->getId()] = $page;

            $this->childWalker($page, $currentPage, $roots);
        }

        return $roots;
    }

    private function childWalker(PageInterface $page, PageInterface $currentPage = null, &$roots, $level = 1)
    {
        foreach ($page->getChildren() as $child) {
            if ($currentPage && $currentPage->getId() == $child->getId()) {
                continue;
            }

            if ($child->isDynamic()) {
                continue;
            }

            $roots[$child->getId()] = $child;

            $this->childWalker($child, $currentPage, $roots, $level + 1);
        }
    }
}