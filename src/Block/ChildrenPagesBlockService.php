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
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\PageBundle\CmsManager\CmsManagerSelectorInterface;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Form\Type\PageSelectorType;
use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Render children pages.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class ChildrenPagesBlockService extends AbstractBlockService
{
    private SiteSelectorInterface $siteSelector;

    private CmsManagerSelectorInterface $cmsManagerSelector;

    public function __construct(Environment $twig, SiteSelectorInterface $siteSelector, CmsManagerSelectorInterface $cmsManagerSelector)
    {
        parent::__construct($twig);

        $this->siteSelector = $siteSelector;
        $this->cmsManagerSelector = $cmsManagerSelector;
    }

    public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
    {
        $settings = $blockContext->getSettings();

        $cmsManager = $this->cmsManagerSelector->retrieve();

        if ($settings['current']) {
            $page = $cmsManager->getCurrentPage();
        } elseif ($settings['pageId']) {
            $page = $settings['pageId'];
        } else {
            $page = false;
            try {
                $site = $this->siteSelector->retrieve();
                if (null !== $site) {
                    $page = $cmsManager->getPage($site, '/');
                }
            } catch (PageNotFoundException $e) {
            }
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'page' => $page,
            'block' => $blockContext->getBlock(),
            'settings' => $settings,
        ], $response);
    }

    public function buildEditForm(FormMapper $form, BlockInterface $block): void
    {
        $form->add('settings', ImmutableArrayType::class, [
            'keys' => [
                ['title', TextType::class, [
                    'required' => false,
                    'label' => 'form.label_title',
                ]],
                ['translation_domain', TextType::class, [
                    'label' => 'form.label_translation_domain',
                    'required' => false,
                ]],
                ['icon', TextType::class, [
                    'label' => 'form.label_icon',
                    'required' => false,
                ]],
                ['current', CheckboxType::class, [
                    'required' => false,
                    'label' => 'form.label_current',
                ]],
                ['pageId', PageSelectorType::class, [
                    'model_manager' => $form->getAdmin()->getModelManager(),
                    'class' => $form->getAdmin()->getClass(),
                    'site' => $block->getPage()->getSite(),
                    'required' => false,
                    'label' => 'form.label_page',
                ]],
                ['class', TextType::class, [
                    'required' => false,
                    'label' => 'form.label_class',
                ]],
            ],
            'translation_domain' => 'SonataPageBundle',
        ]);
    }

    public function getName(): string
    {
        return 'Children Page (core)';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'current' => true,
            'pageId' => null,
            'title' => null,
            'translation_domain' => null,
            'icon' => null,
            'class' => null,
            'template' => '@SonataPage/Block/block_core_children_pages.html.twig',
        ]);
    }

    public function prePersist(BlockInterface $block): void
    {
        $block->setSetting('pageId', \is_object($block->getSetting('pageId')) ? $block->getSetting('pageId')->getId() : null);
    }

    public function preUpdate(BlockInterface $block): void
    {
        $block->setSetting('pageId', \is_object($block->getSetting('pageId')) ? $block->getSetting('pageId')->getId() : null);
    }

    public function load(BlockInterface $block): void
    {
        if (is_numeric($block->getSetting('pageId', null))) {
            $cmsManager = $this->cmsManagerSelector->retrieve();
            $site = $block->getPage()->getSite();

            $block->setSetting('pageId', $cmsManager->getPage($site, $block->getSetting('pageId')));
        }
    }
}
