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
use Sonata\BlockBundle\Block\Service\AbstractAdminBlockService;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\PageBundle\Model\Page;
use Sonata\PageBundle\Model\PageManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageListBlockService extends AbstractAdminBlockService
{
    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @param string $name
     */
    public function __construct($name, EngineInterface $templating, PageManagerInterface $pageManager)
    {
        parent::__construct($name, $templating);

        $this->pageManager = $pageManager;
    }

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block): void
    {
        $formMapper->add('settings', ImmutableArrayType::class, [
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

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $pageList = $this->pageManager->findBy([
            'routeName' => Page::PAGE_ROUTE_CMS_NAME,
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

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataPageBundle', [
            'class' => 'fa fa-home',
        ]);
    }
}
