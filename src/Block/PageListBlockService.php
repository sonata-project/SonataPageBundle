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

namespace Sonata\PageBundle\Block;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Block\Service\EditableBlockService;
use Sonata\BlockBundle\Form\Mapper\FormMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Meta\MetadataInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

final class PageListBlockService extends AbstractBlockService implements EditableBlockService
{
    private PageManagerInterface $pageManager;

    public function __construct(
        Environment $twig,
        PageManagerInterface $pageManager
    ) {
        parent::__construct($twig);

        $this->pageManager = $pageManager;
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $pageList = $this->pageManager->findBy([
            'routeName' => PageInterface::PAGE_ROUTE_CMS_NAME,
        ]);

        $systemElements = $this->pageManager->findBy([
            'url' => null,
            'parent' => null,
        ]);

        return $this->renderResponse($blockContext->getTemplate(), [
            'context' => $blockContext,
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
            'elements' => $pageList,
            'systemElements' => $systemElements,
        ], $response);
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mode' => 'public',
            'title' => null,
            'translation_domain' => null,
            'icon' => 'fa fa-globe',
            'class' => null,
            'template' => '@SonataPage/Block/block_pagelist.html.twig',
        ]);
    }

    public function getMetadata(): MetadataInterface
    {
        return new Metadata('sonata.page.block.pagelist', null, null, 'SonataPageBundle', [
            'class' => 'fa fa-home',
        ]);
    }

    public function configureEditForm(FormMapper $form, BlockInterface $block): void
    {
        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['title', TextType::class, [
                    'label' => 'form.label_title',
                    'required' => false,
                ]],
                ['translation_domain', TextType::class, [
                    'label' => 'form.label_translation_domain',
                    'required' => false,
                ]],
                ['icon', TextType::class, [
                    'label' => 'form.label_icon',
                    'required' => false,
                ]],
                ['class', TextType::class, [
                    'label' => 'form.label_class',
                    'required' => false,
                ]],
                ['mode', ChoiceType::class, [
                    'label' => 'form.label_mode',
                    'choices' => [
                        'public' => 'form.choice_public',
                        'admin' => 'form.choice_admin',
                    ],
                ]],
            ],
            'translation_domain' => 'SonataPageBundle',
        ]);
    }

    public function configureCreateForm(FormMapper $form, BlockInterface $block): void
    {
        $this->configureEditForm($form, $block);
    }

    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
    }
}
