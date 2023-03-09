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

use Sonata\PageBundle\Page\PageServiceManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Olivier Paradis <paradis.olivier@gmail.com>
 *
 * @psalm-suppress MissingTemplateParam
 */
final class PageTypeChoiceType extends AbstractType
{
    public function __construct(private PageServiceManagerInterface $manager)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $defaults = [
            'choices' => $this->getPageTypes(),
            'choice_translation_domain' => false,
        ];

        $resolver->setDefaults($defaults);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_page_type_choice';
    }

    /**
     * @return array<string>
     */
    private function getPageTypes(): array
    {
        $services = $this->manager->getAll();
        $types = [];
        foreach ($services as $id => $service) {
            $types[$service->getName()] = $id;
        }

        ksort($types);

        return $types;
    }
}
