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

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

final class PageListBlockService extends AbstractBlockService
{
    private PageManagerInterface $pageManager;

    private string $name;

    public function __construct(string $name, Environment $twig, PageManagerInterface $pageManager)
    {
        parent::__construct($twig);

        $this->name = $name;
        $this->pageManager = $pageManager;
    }

    public function buildEditForm(FormMapper $form): void
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

    public function getBlockMetadata($code = null): Metadata
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), null, 'SonataPageBundle', [
            'class' => 'fa fa-home',
        ]);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
