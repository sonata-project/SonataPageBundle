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
            'choices' => static fn (Options $opts) => $that->getChoices($opts),
            'choice_translation_domain' => false,
            'filter_choice' => [
                'current_page' => false,
                'request_method' => 'GET',
                'dynamic' => true,
                'hierarchy' => 'all',
            ],
        ]);
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
     * @param Options<array{
     *     page: PageInterface|null,
     *     site: SiteInterface|null,
     *     choice_translation_domain: bool,
     *     filter_choice: array{current_page: false, request_method: string, dynamic: bool, hierarchy: string},
     * }> $options
     *
     * @return array<PageInterface>
     */
    private function getChoices(Options $options): array
    {
        \PHPStan\dumpType($options['site']);
        if (!$options['site'] instanceof SiteInterface) {
            return [];
        }

        $filter_choice = $options['filter_choice'] + [
            'current_page' => false,
            'request_method' => 'GET',
            'dynamic' => true,
            'hierarchy' => 'all',
        ];

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
                    ('root' !== $filter_choice['hierarchy'] || null !== $page->getParent()) &&
                    ('children' !== $filter_choice['hierarchy'] || null === $page->getParent())
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

            $id = $page->getId();
            \assert(null !== $id);

            $choices[$id] = $page;

            $this->childWalker($page, $options['page'], $choices);
        }

        return $choices;
    }

    /**
     * @param WorkingOptions<array{
     *     page: PageInterface|null,
     *     site: SiteInterface|null,
     *     choice_translation_domain: bool,
     *     filter_choice: array{current_page: false, request_method: string, dynamic: bool, hierarchy: string},
     * }> $options
     *
     * @return array<PageInterface>
     */
    private function workingGetChoices(WorkingOptions $options): array
    {
        \PHPStan\dumpType($options['site']);
        return [];
    }

    /**
     * @param array<PageInterface> $choices
     */
    private function childWalker(PageInterface $page, ?PageInterface $currentPage, array &$choices, int $level = 1): void
    {
        foreach ($page->getChildren() as $child) {
            if (null !== $currentPage && $currentPage->getId() === $child->getId()) {
                continue;
            }

            if ($child->isDynamic()) {
                continue;
            }

            $id = $child->getId();
            \assert(null !== $id);

            $choices[$id] = $child;

            $this->childWalker($child, $currentPage, $choices, $level + 1);
        }
    }
}

/**
 * @template TArray of array
 * @extends \ArrayAccess<key-of<TArray>, value-of<TArray>>
 */
interface WorkingOptions extends \ArrayAccess {
    /**
     * @param key-of<TArray> $offset
     */
    public function offsetExists($offset): bool;

    /**
     * @template TOffset of key-of<TArray>
     * @param TOffset $offset
     * @return TArray[TOffset]
     */
    public function offsetGet($offset);

    /**
     * @template TOffset of key-of<TArray>
     * @param TOffset $offset
     * @param TArray[TOffset] $value
     */
    public function offsetSet($offset, $value): void;

    /**
     * @param key-of<TArray> $offset
     */
    public function offsetUnset($offset): void;
}
