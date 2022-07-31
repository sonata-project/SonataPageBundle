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

use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Select a page.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class PageSelectorType extends AbstractType
{
    private PageManagerInterface $manager;

    public function __construct(PageManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $that = $this;

        $resolver->setDefaults([
            'page' => null,
            'site' => null,
            'choices' => static fn (Options $opts, $previousValue) => $that->getChoices($opts),
            'choice_translation_domain' => false,
            'filter_choice' => [
                'current_page' => false,
                'request_method' => 'GET',
                'dynamic' => true,
                'hierarchy' => 'all',
            ],
        ]);
    }

    /**
     * @return array
     */
    public function getChoices(Options $options)
    {
        if (!$options['site'] instanceof SiteInterface) {
            return [];
        }

        $filter_choice = array_merge([
            'current_page' => false,
            'request_method' => 'GET',
            'dynamic' => true,
            'hierarchy' => 'all',
        ], $options['filter_choice']);

        $pages = $this->manager->loadPages($options['site']);

        $choices = [];

        foreach ($pages as $page) {
            // internal cannot be selected
            if ($page->isInternal()) {
                continue;
            }

            if (!$filter_choice['current_page'] && $options['page'] && $options['page']->getId() === $page->getId()) {
                continue;
            }

            if (
                'all' !== $filter_choice['hierarchy'] && (
                    ('root' !== $filter_choice['hierarchy'] || $page->getParent()) &&
                    ('children' !== $filter_choice['hierarchy'] || !$page->getParent())
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

            if ('all' !== $filter_choice['request_method'] && !$page->hasRequestMethod($filter_choice['request_method'])) {
                continue;
            }

            $choices[$page->getId()] = $page;

            $this->childWalker($page, $options['page'], $choices);
        }

        return $choices;
    }

    public function getParent(): string
    {
        return ModelType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_page_selector';
    }

    /**
     * @param PageInterface $currentPage
     * @param array         $choices
     * @param int           $level
     */
    private function childWalker(PageInterface $page, ?PageInterface $currentPage, &$choices, $level = 1): void
    {
        foreach ($page->getChildren() as $child) {
            if ($currentPage && $currentPage->getId() === $child->getId()) {
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
