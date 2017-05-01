<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Form\Type;

use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Select a page.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class PageSelectorType extends AbstractType
{
    /**
     * @var PageManagerInterface
     */
    protected $manager;

    /**
     * @param PageManagerInterface $manager
     */
    public function __construct(PageManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $that = $this;

        $resolver->setDefaults(array(
            'page' => null,
            'site' => null,
            'choice_list' => function (Options $opts, $previousValue) use ($that) {
                return class_exists('Symfony\Component\Form\ChoiceList\ArrayChoiceList') ?
                    new ArrayChoiceList($that->getChoices($opts)) :
                    new SimpleChoiceList($that->getChoices($opts)); // NEXT_MAJOR: remove condition
            },
            'choice_translation_domain' => false,
            'filter_choice' => array(
                'current_page' => false,
                'request_method' => 'GET',
                'dynamic' => true,
                'hierarchy' => 'all',
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * @param Options $options
     *
     * @return array
     */
    public function getChoices(Options $options)
    {
        if (!$options['site'] instanceof SiteInterface) {
            return array();
        }

        $filter_choice = array_merge(array(
            'current_page' => false,
            'request_method' => 'GET',
            'dynamic' => true,
            'hierarchy' => 'all',
        ), $options['filter_choice']);

        $pages = $this->manager->loadPages($options['site']);

        $choices = array();

        foreach ($pages as $page) {
            // internal cannot be selected
            if ($page->isInternal()) {
                continue;
            }

            if (!$filter_choice['current_page'] && $options['page'] && $options['page']->getId() == $page->getId()) {
                continue;
            }

            if (
                'all' != $filter_choice['hierarchy'] && (
                    ('root' != $filter_choice['hierarchy'] || $page->getParent()) &&
                    ('children' != $filter_choice['hierarchy'] || !$page->getParent())
                )
            ) {
                continue;
            }

            if ('all' !== $filter_choice['dynamic'] && (
                    ($filter_choice['dynamic'] && $page->isDynamic()) ||
                    (!$filter_choice['dynamic'] && !$page->isDynamic())
                )
            ) {
                continue;
            }

            if ('all' != $filter_choice['request_method'] && !$page->hasRequestMethod($filter_choice['request_method'])) {
                continue;
            }

            $choices[$page->getId()] = $page;

            $this->childWalker($page, $options['page'], $choices);
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'sonata_type_model';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_page_selector';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * @param PageInterface $page
     * @param PageInterface $currentPage
     * @param array         $choices
     * @param int           $level
     */
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
