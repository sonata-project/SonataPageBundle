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

class PageSelectorType extends ModelType
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
            'parent'            => 'choice',
            'preferred_choices' => array(),
            'page'              => null,
            'filter_choice'     => array('current_page' => false, 'request_method' => 'GET', 'dynamic' => true, 'hierarchy' => 'all'),
        );

        $options = array_replace($defaultOptions, $options);

        if(!isset($options['choices'])) {
            $options['filter_choice'] = isset($options['filter_choice']) ? array_replace($defaultOptions['filter_choice'], $options['filter_choice']) : $defaultOptions['filter_choice'];
            $options['choices'] = $this->getParentChoices($options);
        }

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

    public function getParentChoices($options = null)
    {
        $pages = $this->manager->loadPages();

        $choices = array();

        foreach ($pages as $page) {
            if (!$options['filter_choice']['current_page'] && $options['page'] && $options['page']->getId() == $page->getId()) {
                continue;
            }

            if (
                'all' != $options['filter_choice']['hierarchy'] && (
                    ('root' != $options['filter_choice']['hierarchy'] || $page->getParent()) &&
                    ('children' != $options['filter_choice']['hierarchy'] || !$page->getParent())
                )
            ) {
                continue;
            }

            if ('all' != $options['filter_choice']['dynamic'] && (
                    ($options['filter_choice']['dynamic'] && $page->isDynamic()) ||
                    (!$options['filter_choice']['dynamic'] && !$page->isDynamic())
                )
            ) {
                continue;
            }

            if ('all' != $options['filter_choice']['request_method'] && !$page->hasRequestMethod($options['filter_choice']['request_method'])) {
                continue;
            }

            $choices[$page->getId()] = $page;

            $this->childWalker($page, $options['page'], $choices);
        }

        return $choices;
    }

    private function childWalker(PageInterface $page, PageInterface $currentPage = null, &$choices, $level = 1)
    {
        foreach ($page->getChildren() as $child) {
            if ($currentPage && $currentPage->getId() == $child->getId()) {
                continue;
            }

            if ($child->isDynamic()) {
                continue;
            }

            $choices[$child->getId()] = $child;

            $this->childWalker($child, $currentPage, $choices, $level + 1);
        }
    }
}